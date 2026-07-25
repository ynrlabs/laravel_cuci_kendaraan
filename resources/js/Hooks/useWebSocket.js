import { useEffect, useState, useCallback, useRef } from 'react';
import { useAuth } from './useAuth';
import Echo from 'laravel-echo';

export function useWebSocket() {
    const { user } = useAuth();
    const [echo, setEcho] = useState(null);
    const [connected, setConnected] = useState(false);
    const [error, setError] = useState(null);
    const reconnectAttempts = useRef(0);
    const maxReconnectAttempts = 5;

    useEffect(() => {
        if (!user) return;

        // Initialize Echo connection
        const echoInstance = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT,
            wssPort: import.meta.env.VITE_REVERB_PORT,
            forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('access_token')}`,
                },
            },
        });

        // Connection event handlers
        echoInstance.connector.pusher.connection.bind('connected', () => {
            console.log('WebSocket connected');
            setConnected(true);
            setError(null);
            reconnectAttempts.current = 0;
        });

        echoInstance.connector.pusher.connection.bind('disconnected', () => {
            console.log('WebSocket disconnected');
            setConnected(false);
        });

        echoInstance.connector.pusher.connection.bind('error', (err) => {
            console.error('WebSocket error:', err);
            setError(err.message || 'WebSocket connection error');
            
            // Attempt reconnection
            if (reconnectAttempts.current < maxReconnectAttempts) {
                reconnectAttempts.current++;
                setTimeout(() => {
                    echoInstance.connector.pusher.connect();
                }, Math.min(1000 * Math.pow(2, reconnectAttempts.current), 30000));
            }
        });

        setEcho(echoInstance);

        return () => {
            echoInstance.disconnect();
        };
    }, [user]);

    // Subscribe to a private channel
    const subscribe = useCallback((channelName, eventName, callback) => {
        if (!echo) return () => {};

        const channel = echo.private(channelName);
        
        channel.listen(eventName, (data) => {
            callback(data);
        });

        // Return unsubscribe function
        return () => {
            channel.stopListening(eventName);
        };
    }, [echo]);

    // Subscribe to booking channel
    const subscribeToBooking = useCallback((bookingId, callback) => {
        return subscribe(`booking.${bookingId}`, 'booking.status_updated', callback);
    }, [subscribe]);

    const subscribeToBookingCreated = useCallback((bookingId, callback) => {
        return subscribe(`booking.${bookingId}`, 'booking.created', callback);
    }, [subscribe]);

    const subscribeToBookingConfirmed = useCallback((bookingId, callback) => {
        return subscribe(`booking.${bookingId}`, 'booking.confirmed', callback);
    }, [subscribe]);

    const subscribeToBookingCancelled = useCallback((bookingId, callback) => {
        return subscribe(`booking.${bookingId}`, 'booking.cancelled', callback);
    }, [subscribe]);

    const subscribeToPaymentReceived = useCallback((bookingId, callback) => {
        return subscribe(`booking.${bookingId}`, 'payment.received', callback);
    }, [subscribe]);

    // Subscribe to branch queue channel (for kasir/operator)
    const subscribeToBranchQueue = useCallback((branchId, callback) => {
        return subscribe(`branch.${branchId}.queue`, 'booking.created', callback);
    }, [subscribe]);

    const subscribeToBranchQueueStatus = useCallback((branchId, callback) => {
        return subscribe(`branch.${branchId}.queue`, 'booking.status_updated', callback);
    }, [subscribe]);

    // Subscribe to branch stats channel (for admin)
    const subscribeToBranchStats = useCallback((branchId, callback) => {
        return subscribe(`branch.${branchId}.stats`, 'booking.confirmed', callback);
    }, [subscribe]);

    const subscribeToBranchStatsStatus = useCallback((branchId, callback) => {
        return subscribe(`branch.${branchId}.stats`, 'booking.status_updated', callback);
    }, [subscribe]);

    const subscribeToBranchStatsPayment = useCallback((branchId, callback) => {
        return subscribe(`branch.${branchId}.stats`, 'payment.received', callback);
    }, [subscribe]);

    const subscribeToBranchStatsCancelled = useCallback((branchId, callback) => {
        return subscribe(`branch.${branchId}.stats`, 'booking.cancelled', callback);
    }, [subscribe]);

    // Subscribe to user notification channel
    const subscribeToNotifications = useCallback((callback) => {
        if (!user?.id) return () => {};
        return subscribe(`App.Models.User.${user.id}`, 'notification', callback);
    }, [subscribe, user?.id]);

    return {
        echo,
        connected,
        error,
        subscribe,
        subscribeToBooking,
        subscribeToBookingCreated,
        subscribeToBookingConfirmed,
        subscribeToBookingCancelled,
        subscribeToPaymentReceived,
        subscribeToBranchQueue,
        subscribeToBranchQueueStatus,
        subscribeToBranchStats,
        subscribeToBranchStatsStatus,
        subscribeToBranchStatsPayment,
        subscribeToBranchStatsCancelled,
        subscribeToNotifications,
    };
}

export function useBranchQueue(branchId) {
    const { subscribeToBranchQueue, subscribeToBranchQueueStatus } = useWebSocket();
    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!branchId) return;

        setLoading(true);
        
        // Subscribe to new bookings
        const unsubscribeCreated = subscribeToBranchQueue(branchId, (data) => {
            console.log('New booking received:', data);
            setBookings(prev => {
                // Check if already exists
                if (prev.some(b => b.booking.id === data.booking.id)) {
                    return prev;
                }
                return [data, ...prev];
            });
        });

        // Subscribe to status updates
        const unsubscribeStatus = subscribeToBranchQueueStatus(branchId, (data) => {
            console.log('Booking status updated:', data);
            setBookings(prev => prev.map(item => 
                item.booking.id === data.booking.id ? data : item
            ));
        });

        return () => {
            unsubscribeCreated();
            unsubscribeStatus();
            setLoading(false);
        };
    }, [branchId, subscribeToBranchQueue, subscribeToBranchQueueStatus]);

    return { bookings, loading };
}

export function useBranchStats(branchId) {
    const { 
        subscribeToBranchStats, 
        subscribeToBranchStatsStatus,
        subscribeToBranchStatsPayment,
        subscribeToBranchStatsCancelled 
    } = useWebSocket();
    const [stats, setStats] = useState({
        totalBookings: 0,
        confirmed: 0,
        inProgress: 0,
        completed: 0,
        cancelled: 0,
        revenue: 0,
    });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!branchId) return;

        setLoading(true);

        const unsubscribers = [
            subscribeToBranchStats(branchId, (data) => {
                console.log('Booking confirmed stats:', data);
                setStats(prev => ({
                    ...prev,
                    totalBookings: prev.totalBookings + 1,
                    confirmed: prev.confirmed + 1,
                }));
            }),
            subscribeToBranchStatsStatus(branchId, (data) => {
                console.log('Booking status updated stats:', data);
                const newStatus = data.booking.new_status || data.booking.status;
                const oldStatus = data.booking.old_status;
                
                setStats(prev => {
                    const updated = { ...prev };
                    if (oldStatus) {
                        updated[oldStatus] = Math.max(0, (updated[oldStatus] || 1) - 1);
                    }
                    updated[newStatus] = (updated[newStatus] || 0) + 1;
                    return updated;
                });
            }),
            subscribeToBranchStatsPayment(branchId, (data) => {
                console.log('Payment received stats:', data);
                setStats(prev => ({
                    ...prev,
                    revenue: prev.revenue + (data.payment?.amount || 0),
                }));
            }),
            subscribeToBranchStatsCancelled(branchId, (data) => {
                console.log('Booking cancelled stats:', data);
                setStats(prev => ({
                    ...prev,
                    cancelled: prev.cancelled + 1,
                }));
            }),
        ];

        return () => {
            unsubscribers.forEach(unsub => unsub());
            setLoading(false);
        };
    }, [branchId, subscribeToBranchStats, subscribeToBranchStatsStatus, subscribeToBranchStatsPayment, subscribeToBranchStatsCancelled]);

    return { stats, loading };
}

export function useBookingStatus(bookingId) {
    const { 
        subscribeToBookingCreated,
        subscribeToBookingConfirmed,
        subscribeToBookingCancelled,
        subscribeToPaymentReceived,
        subscribeToBooking 
    } = useWebSocket();
    const [status, setStatus] = useState(null);
    const [booking, setBooking] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!bookingId) return;

        setLoading(true);

        const unsubscribers = [
            subscribeToBookingCreated(bookingId, (data) => {
                console.log('Booking created:', data);
                setBooking(data.booking);
                setStatus(data.booking.status);
            }),
            subscribeToBookingConfirmed(bookingId, (data) => {
                console.log('Booking confirmed:', data);
                setBooking(data.booking);
                setStatus(data.booking.status);
            }),
            subscribeToBookingCancelled(bookingId, (data) => {
                console.log('Booking cancelled:', data);
                setBooking(data.booking);
                setStatus(data.booking.status);
            }),
            subscribeToPaymentReceived(bookingId, (data) => {
                console.log('Payment received:', data);
                setBooking(prev => ({ ...prev, payment: data.payment }));
            }),
            subscribeToBooking(bookingId, (data) => {
                console.log('Booking status updated:', data);
                setBooking(data.booking);
                setStatus(data.booking.status);
            }),
        ];

        return () => {
            unsubscribers.forEach(unsub => unsub());
            setLoading(false);
        };
    }, [bookingId, subscribeToBookingCreated, subscribeToBookingConfirmed, subscribeToBookingCancelled, subscribeToPaymentReceived, subscribeToBooking]);

    return { status, booking, loading };
}