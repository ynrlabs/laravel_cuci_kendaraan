<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Models\Setting;
use App\Models\Branch;
use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class GlobalSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Global';

    protected static ?string $title = 'Pengaturan Global';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.global-settings';

    public ?array $data = [];

    public function mount(): void
    {
        // Check permission
        abort_unless(static::canAccess(), 403);

        // Load settings from database
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Decode operational hours if exists
        if (isset($settings['operational_hours'])) {
            $settings['operational_hours'] = json_decode($settings['operational_hours'], true);
        }

        // Load primary branch information (or the first branch) to populate general info
        $branch = Branch::first();
        if ($branch) {
            $settings['branch_name'] = $branch->name;
            $settings['store_id'] = $branch->slug; // Readonly store slug/id
            $settings['address'] = $branch->address;
            $settings['phone'] = $branch->phone;
            $settings['email'] = $branch->email;
            $settings['city'] = $branch->city;
            $settings['province'] = $branch->province;
            $settings['postal_code'] = $branch->postal_code;
        }

        $this->form->rawState($settings);
    }

    public function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema)
            ->statePath('data');
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        // 1. Separate branch data and global setting data
        $branchFields = ['branch_name', 'address', 'phone', 'email', 'city', 'province', 'postal_code'];
        
        // 2. Save branch data (to the first branch)
        $branch = Branch::first();
        if ($branch) {
            $branch->update([
                'name' => $formData['branch_name'] ?? $branch->name,
                'address' => $formData['address'] ?? $branch->address,
                'phone' => $formData['phone'] ?? $branch->phone,
                'email' => $formData['email'] ?? $branch->email,
                'city' => $formData['city'] ?? $branch->city,
                'province' => $formData['province'] ?? $branch->province,
                'postal_code' => $formData['postal_code'] ?? $branch->postal_code,
            ]);
        }

        // 3. Save other settings to Settings table
        foreach ($formData as $key => $value) {
            // Skip branch fields as they are saved in branch table
            if (in_array($key, $branchFields) || $key === 'store_id') {
                continue;
            }

            // Encode array/json values (like operational_hours)
            if (is_array($value)) {
                $value = json_encode($value);
            }

            Setting::where('key', $key)->update(['value' => $value]);
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin_branch'));
    }
}

