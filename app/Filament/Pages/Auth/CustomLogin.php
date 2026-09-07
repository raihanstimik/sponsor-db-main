<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

// app/Filament/Pages/Auth/CustomLogin.php — Login ala Stitch "Masuk Sistem":
// heading + placeholder + ikon + tombol navy + link daftar (logika auth tetap bawaan).
class CustomLogin extends Login
{
    public function getHeading(): string|Htmlable|null
    {
        return 'Manajemen data Sponsor ICM';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function registerAction(): Action
    {
        return parent::registerAction()->label('daftar Akun Baru');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->prefixIcon(Heroicon::OutlinedEnvelope)
            ->placeholder('email');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi Akun')
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="-1"> {{ __(\'filament-panels::auth/pages/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->prefixIcon(Heroicon::OutlinedLockClosed)
            ->placeholder('Masukkan kata sandi Anda');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Login');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
                $this->getFormContentComponent(),
                $this->getMultiFactorChallengeFormContentComponent(),
                Html::make(fn (): HtmlString => new HtmlString(
                    '<div class="text-center text-sm text-gray-500">Belum memiliki akun? '.$this->registerAction->toHtml().'</div>'
                ))->visible(fn (): bool => filament()->hasRegistration() && blank($this->userUndertakingMultiFactorAuthentication)),
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
            ]);
    }
}
