<?php

namespace App\DataGrids\Admin;

use App\Models\ContractTemplate;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ContractTemplateDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('contract_templates')
            ->select(
                'contract_templates.id',
                'contract_templates.title',
                'contract_templates.identifier',
                'contract_templates.category',
                'contract_templates.version',
                'contract_templates.original_filename',
                'contract_templates.storage_path',
                'contract_templates.file_size',
                'contract_templates.status',
                'contract_templates.member_accessible',
                'contract_templates.effective_date',
                'contract_templates.created_at'
            );

        $this->addFilter('id', 'contract_templates.id');
        $this->addFilter('title', 'contract_templates.title');
        $this->addFilter('identifier', 'contract_templates.identifier');
        $this->addFilter('category', 'contract_templates.category');
        $this->addFilter('status', 'contract_templates.status');
        $this->addFilter('member_accessible', 'contract_templates.member_accessible');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => 'Template Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $categoryLabel = ContractTemplate::CATEGORIES[$row->category] ?? ucfirst(str_replace('_', ' ', $row->category));
                return '<div class="flex flex-col"><span class="font-medium text-gray-800 dark:text-white">' . e($row->title) . '</span><span class="text-xs text-gray-500">' . e($categoryLabel) . ' (' . e($row->identifier) . ')</span></div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'version',
            'label'      => 'Version',
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                return '<span class="font-mono text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800">v' . e($row->version ?: '1.0') . '</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'original_filename',
            'label'      => 'Attached File',
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
            'closure'    => function ($row) {
                if (empty($row->storage_path)) {
                    return '<span class="text-xs text-amber-600 font-medium">Pending Upload (Draft)</span>';
                }

                $bytes = (int) $row->file_size;
                $sizeStr = $bytes >= 1048576
                    ? number_format($bytes / 1048576, 2) . ' MB'
                    : number_format($bytes / 1024, 1) . ' KB';

                return '<div class="flex flex-col"><span class="text-xs font-mono truncate max-w-xs">' . e($row->original_filename) . '</span><span class="text-[11px] text-gray-500">' . $sizeStr . '</span></div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => 'Status',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->status === ContractTemplate::STATUS_PUBLISHED) {
                    return '<span class="label-active">Published</span>';
                }
                if ($row->status === ContractTemplate::STATUS_ARCHIVED) {
                    return '<span class="label-canceled">Archived</span>';
                }
                return '<span class="label-pending">Draft</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'member_accessible',
            'label'      => 'Member Visible',
            'type'       => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->member_accessible
                    ? '<span class="text-xs font-medium text-emerald-600">Yes (Public/Members)</span>'
                    : '<span class="text-xs font-medium text-gray-400">No (Admin Only)</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Created',
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'index'  => 'download',
            'icon'   => 'icon-download',
            'title'  => 'Download Template',
            'method' => 'GET',
            'url'    => function ($row) {
                if (!empty($row->storage_path)) {
                    return route('admin.b2b.contract-templates.download', $row->id);
                }
                return '#';
            },
        ]);

        $this->addAction([
            'index'  => 'edit',
            'icon'   => 'icon-edit',
            'title'  => 'Edit Template',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.b2b.contract-templates.edit', $row->id);
            },
        ]);

        $this->addAction([
            'index'  => 'delete',
            'icon'   => 'icon-delete',
            'title'  => 'Delete Template',
            'method' => 'POST',
            'url'    => function ($row) {
                return route('admin.b2b.contract-templates.delete', $row->id);
            },
        ]);
    }
}

