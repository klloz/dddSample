<?php

namespace App\Providers;

use Doctrine\Persistence\ManagerRegistry;
use Domains\Accounts\Models\Company\CompanyAccount;
use Domains\Accounts\Models\User\PasswordReset;
use Domains\Accounts\Models\User\User;
use Domains\Accounts\Repositories\CompanyAccountRepositoryContract;
use Domains\Accounts\Repositories\PasswordResetRepositoryContract;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Activities\Models\Activity;
use Domains\Activities\Repositories\ActivityRepositoryContract;
use Domains\AddressBook\Models\Company\Company;
use Domains\AddressBook\Models\Contact\Contact;
use Domains\AddressBook\Repositories\CompanyRepositoryContract;
use Domains\AddressBook\Repositories\ContactRepositoryContract;
use Domains\Calendar\Models\Event\BaseEvent;
use Domains\Calendar\Repositories\EventParticipantRepositoryContract;
use Domains\Calendar\Repositories\EventRepositoryContract;
use Domains\Common\Models\Dictionaries\Industry;
use Domains\Common\Repositories\IndustryRepositoryContract;
use Domains\Dashboard\Models\Dashboard\Dashboard;
use Domains\Dashboard\Repositories\Dashboard\DashboardRepositoryContract;
use Domains\Dashboard\Repositories\Dashboard\WidgetDataRepositoryContract;
use Domains\Dashboard\Repositories\Reports\LeadsJourneyRepositoryContract;
use Domains\Dashboard\Repositories\Reports\LeadsSummaryRepositoryContract;
use Domains\Dashboard\Repositories\Reports\LeadsVelocityRepositoryContract;
use Domains\Dashboard\Repositories\Reports\SalesSummaryRepositoryContract;
use Domains\Imports\Models\ImportJob;
use Domains\Imports\Repositories\ImportJobRepositoryContract;
use Domains\Marketing\Models\Sequence\Contact\SequenceContact;
use Domains\Marketing\Models\Sequence\Sequence;
use Domains\Marketing\Models\Template\Template;
use Domains\Marketing\Repositories\SequenceContactRepositoryContract;
use Domains\Marketing\Repositories\SequenceRepositoryContract;
use Domains\Marketing\Repositories\TemplateRepositoryContract;
use Domains\Notes\Models\Note;
use Domains\Notes\Repositories\NoteRepositoryContract;
use Domains\Notifications\Models\Notification;
use Domains\Notifications\Repositories\NotificationRepositoryContract;
use Domains\Sales\Models\Lead\Lead;
use Domains\Sales\Models\Product\Product;
use Domains\Sales\Models\ProductCategory\ProductCategory;
use Domains\Sales\Models\Workflow\Workflow;
use Domains\Sales\Repositories\LeadRepositoryContract;
use Domains\Sales\Repositories\ProductCategoryRepositoryContract;
use Domains\Sales\Repositories\ProductRepositoryContract;
use Domains\Sales\Repositories\WorkflowRepositoryContract;
use Domains\Storage\Models\File;
use Domains\Storage\Repositories\FileRepositoryContract;
use Domains\AdminPanel;
use Infrastructure\Persistence\Doctrine\Repositories;
use LaravelDoctrine\ORM\DoctrineServiceProvider as ServiceProvider;

class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerAdminPanelRepositories();
        $this->registerRepositories();
    }

    /**
     * Register admin panel repositories
     */
    protected function registerAdminPanelRepositories()
    {
        $em = app(ManagerRegistry::class)->getManager('admin_panel');
    }

    /**
     * Register repositories
     */
    protected function registerRepositories(): void
    {
        $this->app->singleton(NotificationRepositoryContract::class, function($app) {
            return new Repositories\Notifications\DoctrineNotificationRepository(
                $app['em'],
                $app['em']->getClassMetaData(Notification::class)
            );
        });
    }
}
