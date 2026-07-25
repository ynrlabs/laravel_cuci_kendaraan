import { usePage } from '@inertiajs/react';

export function useAuth() {
  const { props } = usePage();
  const user = props.auth?.user || null;

  const logout = () => {
    window.location.href = '/logout';
  };

  return {
    user,
    logout,
    isAuthenticated: !!user,
  };
}