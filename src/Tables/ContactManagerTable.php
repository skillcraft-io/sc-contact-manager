<?php

namespace Skillcraft\ContactManager\Tables;

use Illuminate\Support\Arr;
use Botble\Base\Facades\Html;
use Botble\Table\Columns\Column;
use Illuminate\Http\JsonResponse;
use Botble\Table\Columns\IdColumn;
use Illuminate\Support\Facades\DB;
use Botble\Base\Facades\BaseHelper;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\NameColumn;
use Illuminate\Support\Facades\Auth;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\CreatedAtColumn;
use Illuminate\Database\Eloquent\Builder;
use Skillcraft\ContactManager\Models\ContactTag;
use Skillcraft\ContactManager\Models\ContactGroup;
use Botble\Table\BulkActions\DeleteBulkAction;
use Skillcraft\ContactManager\Models\ContactManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ContactManagerTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ContactManager::class)
            ->addActions([
                EditAction::make()
                    ->route('contact-manager.edit'),
                DeleteAction::make()
                    ->route('contact-manager.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ContactManager $item) {
                if (! $this->hasPermission('contact-manager.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('contact-manager.edit', $item->getKey()), BaseHelper::clean($item->name));
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
               'id',
               'first_name',
               'last_name',
               'created_at',
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('first_name'),
            Column::make('last_name'),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('contact-manager.create'), 'contact-manager.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('contact-manager.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }

    public function getFilters(): array
    {
        return [
            'contact_manager.first_name' => [
                'title'    => trans('plugins/contact-manager::contact-manager.forms.first_name'),
                'type'     => 'text',
                'validate' => 'required|string',
            ],
            'contact_manager.last_name' => [
                'title'    => trans('plugins/contact-manager::contact-manager.forms.last_name'),
                'type'     => 'text',
                'validate' => 'required|string',
            ],
            'contact_manager.group_id' => [
                'title'    => trans('plugins/contact-manager::contact-manager.forms.group'),
                'type'     => 'select',
                'validate' => 'required|integer',
                'choices'  => ['0' => trans('plugins/contacts-manager::contacts-manager.no_group')] + (new ContactGroup())->query()->pluck('name', 'id')->toArray(),
            ],
            'contact_addresses.state' => [
                'title'    => trans('plugins/contact-manager::contact-manager.forms.state'),
                'type'     => 'text',
                'validate' => 'required|string',
            ],
            'contact_tags' => [
                'title'    => trans('plugins/contact-manager::contact-manager.forms.tag'),
                'type'     => 'select-search',
                'validate' => 'required|integer',
                'choices'  => (new ContactTag())
                    ->query()
                    ->pluck('name', 'id')
                    ->toArray(),
            ],
        ];
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }


    public function applyFilterCondition($query, string $key, string $operator, ?string $value)
    {
        if (strpos($key, '.') !== -1) {
            $key = Arr::last(explode('.', $key));
        }

        switch ($key) {
            case 'contact_addresses.state':
            case 'contact_addresses.address':
            case 'contact_addresses.address2':
            case 'contact_addresses.postalcode':
            case 'contact_addresses.city':
                if (!$value) {
                    break;
                }

                if ($operator === 'like') {
                    $value = '%'.$value.'%';
                }
            
                $query = $query->whereHas('address_info', function (Builder $query) use ($key, $operator, $value) {
                    $query->where('contact_addresses.'.$key, $operator, $value);
                });

                break;
            case 'contact_tags':
                    $query = $query->whereExists(function ($query) use ($operator, $value) {
                        $query->select(DB::raw(1))
                              ->from('contacts_tags')
                              ->whereRaw('contact_manager.id = contacts_tags.contact_id')
                              ->where('contacts_tags.tag_id', $operator, $value);
                    });
                break;
            default:
                $query = parent::applyFilterCondition($query, $key, $operator, $value);
        }

        return $query;
    }
}
