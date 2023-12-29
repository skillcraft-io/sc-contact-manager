<?php

namespace Skillcraft\ContactManager\Providers;

use Illuminate\Support\Facades\DB;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\CustomField\Facades\CustomField;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Skillcraft\ContactManager\Models\ContactManager;

class ContactManagerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (defined('SKILLCRAFT_CORE_MODULE_SCREEN_NAME')) {
            $this
                ->setNamespace('plugins/contact-manager')
                ->loadHelpers()
                ->loadAndPublishConfigurations(['permissions'])
                ->loadMigrations()
                ->loadAndPublishTranslations()
                ->loadAndPublishViews()
                ->loadRoutes();

            DashboardMenu::default()->beforeRetrieving(function () {
                DashboardMenu::make()->registerItem([
                    'id' => 'cms-plugins-contact-manager',
                    'priority' => 1,
                    'parent_id' => null,
                    'name' => 'plugins/contact-manager::contact-manager.name',
                    'icon' => 'ti ti-user-plus',
                    'url' => null,
                    'permissions' => [],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-contact-manager-2',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-contact-manager',
                    'name' => 'plugins/contact-manager::contact-manager.name',
                    'icon' => null,
                    'url' => route('contact-manager.index'),
                    'permissions' => ['contact-manager.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-contact-manager.models.tag',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-contact-manager',
                    'name' => 'plugins/contact-manager::contact-manager.models.tag.name',
                    'icon' => null,
                    'url' => route('contact-tag.index'),
                    'permissions' => ['contact-tag.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-contact-manager.models.group',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-contact-manager',
                    'name' => 'plugins/contact-manager::contact-manager.models.group.name',
                    'icon' => null,
                    'url' => route('contact-group.index'),
                    'permissions' => ['contact-group.index'],
                ]);
            });

            $this->app->booted(function () {
                if (defined('CUSTOM_FIELD_MODULE_SCREEN_NAME')) {
                    CustomField::registerModule(ContactManager::class)
                    ->registerRule('basic', trans('Contact'), ContactManager::class, function () {
                        return (new ContactManager())
                            ->query()
                            ->pluck(DB::raw("CONCAT(first_name, ' ', last_name)"), 'id')
                            ->toArray();
                    })
                        ->expandRule('other', trans('plugins/custom-field::rules.model_name'), 'model_name', function () {
                            return [
                            ContactManager::class => trans('Contacts'),
                            ];
                        });
                }
            });
        }
    }
}
