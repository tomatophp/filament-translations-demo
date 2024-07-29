<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\LoginPage;
use App\Filament\Pages\Auth\RegisterPage;
use App\Livewire\DiscordOTP;
use App\Livewire\ResetPassword;
use App\Livewire\UpdatePassword;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use TomatoPHP\FilamentAccounts\Filament\Pages\Auth\LoginAccount;
use TomatoPHP\FilamentAccounts\FilamentAccountsSaaSPlugin;
use TomatoPHP\FilamentTranslations\FilamentTranslationsPlugin;
use TomatoPHP\FilamentTranslations\FilamentTranslationsSwitcherPlugin;
use TomatoPHP\FilamentTranslations\Resources\TranslationResource;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->favicon(asset('favicon.ico'))
            ->brandLogo(asset('tomato.png'))
            ->brandLogoHeight('80px')
            ->font(
                'IBM Plex Sans Arabic',
                provider: GoogleFontProvider::class,
            )
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                Pages\Dashboard::class
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->items([
                    NavigationItem::make(Pages\Dashboard::getNavigationLabel())
                        ->icon('heroicon-o-home')
                        ->isActiveWhen(fn (): bool => url()->current() === Pages\Dashboard::getUrl())
                        ->url(fn (): string => Pages\Dashboard::getUrl()),
                    ...TranslationResource::getNavigationItems()
                ])->groups([
                    NavigationGroup::make()
                        ->label('Links')
                        ->items([
                            NavigationItem::make('Documentation')
                                ->icon('bxs-file-doc')
                                ->url("https://filamentphp.com/plugins/3x1io-tomato-translations")
                                ->openUrlInNewTab(),
                            NavigationItem::make('Github')
                                ->icon('bxl-github')
                                ->url("https://www.github.com/tomatophp/filament-translations")
                                ->openUrlInNewTab(),
                            NavigationItem::make('Issue')
                                ->icon('bxs-error')
                                ->url("https://github.com/tomatophp/filament-translations/issues")
                                ->openUrlInNewTab(),
                            NavigationItem::make('Discord')
                                ->icon('bxl-discord')
                                ->url("https://discord.gg/vKV9U7gD3c")
                                ->openUrlInNewTab(),
                            NavigationItem::make('Buy Me a Coffee')
                                ->icon('bxs-coffee')
                                ->url("https://github.com/sponsors/3x1io")
                                ->openUrlInNewTab()
                        ]),
                ]);
            })
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->plugin(
                FilamentAccountsSaaSPlugin::make()
                    ->databaseNotifications()
                    ->checkAccountStatusInLogin()
                    ->APITokenManager()
                    ->editTeam()
                    ->deleteTeam()
                    ->teamInvitation()
                    ->showTeamMembers()
                    ->editProfile()
                    ->editPassword()
                    ->browserSesstionManager()
                    ->deleteAccount()
                    ->editProfileMenu()
                    ->registration()
                    ->useOTPActivation(),
            )
            ->plugin(FilamentTranslationsSwitcherPlugin::make())
            ->plugin(
                FilamentTranslationsPlugin::make()
                    ->allowGoogleTranslateScan()
                    ->allowGPTScan()
                    ->allowCreate()
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->login(LoginPage::class)
            ->registration(RegisterPage::class);
    }
}
