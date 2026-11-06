<?php

namespace App\DataGrids\Admin;

use App\Models\MemberContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class MemberContractDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('member_contracts')
            ->leftJoin('customers', 'member_contracts.customer_id', '=', 'customers.id')
            ->leftJoin('contract_templates', 'member_contracts.template_id', '=', 'contract_templates.id')
            ->select(
                'member_contracts.id',
                'member_contracts.reference_number',
                'member_contracts.title',
                'member_contracts.customer_id',
                'member_contracts.status',
                'member_contracts.issued_date',
                'member_contracts.effective_date',
                'member_contracts.expires_at',
                'member_contracts.file_size',
                'member_contracts.original_filename',
                'member_contracts.created_at',
                DB::raw("CONCAT(COALESCE(customers.first_name, ''), ' ', COALESCE(customers.last_name, '')) as customer_name"),
                'customers.email as customer_email',
                'contract_templates.title as template_title'
            );

        $this->addFilter('id', 'member_contracts.id');
        $this->addFilter('reference_number', 'member_contracts.reference_number');
        $this->addFilter('title', 'member_contracts.title');
        $this->addFilter('status', 'member_contracts.status');
        $this->addFilter('customer_name', DB::raw("CONCAT(COALESCE(customers.first_name, ''), ' ', COALESCE(customers.last_name, ''))"));
        $this->addFilter('customer_email', 'customers.email');

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
            'index'      => 'reference_number',
            'label'      => 'Reference #',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return '<a href="' . route('admin.b2b.member-contracts.view', $row->id) . '" class="font-mono font-medium text-emerald-600 hover:underline">' . e($row->reference_number) . '</a>';
            },
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => 'Agreement Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $sub = $row->template_title ? ('Template: ' . e($row->template_title)) : 'Custom Agreement';
                return '<div class="flex flex-col"><span class="font-medium text-gray-800 dark:text-white">' . e($row->title) . '</span><span class="text-xs text-gray-500">' . $sub . '</span></div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'customer_name',
            'label'      => 'Wholesale Member',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return '<div class="flex flex-col"><span class="font-medium">' . e(trim($row->customer_name) ?: 'Member #' . $row->customer_id) . '</span><span class="text-xs text-gray-500">' . e($row->customer_email) . '</span></div>';
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
                return match ($row->status) {
                    MemberContract::STATUS_ACCEPTED   => '<span class="label-active">Accepted</span>',
                    MemberContract::STATUS_SENT       => '<span class="label-pending">Sent (Pending)</span>',
                    MemberContract::STATUS_REJECTED   => '<span class="label-canceled">Rejected</span>',
                    MemberContract::STATUS_EXPIRED    => '<span class="label-closed">Expired</span>',
                    MemberContract::STATUS_CANCELLED  => '<span class="label-closed">Cancelled</span>',
                    MemberContract::STATUS_SUPERSEDED => '<span class="label-info">Superseded</span>',
                    default                           => '<span class="label-pending">Draft</span>',
                };
            },
        ]);

        $this->addColumn([
            'index'      => 'issued_date',
            'label'      => 'Issued Date',
            'type'       => 'date',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'expires_at',
            'label'      => 'Expiry',
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if (!$row->expires_at) {
                    return '—';
                }
                $isExpired = strtotime($row->expires_at) < time();
                $color = $isExpired ? 'text-red-500 font-semibold' : 'text-gray-600';
                return '<span class="' . $color . '">' . date('M d, Y', strtotime($row->expires_at)) . '</span>';
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
            'index'  => 'view',
            'icon'   => 'icon-eye',
            'title'  => 'View Details & Audit Trail',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.b2b.member-contracts.view', $row->id);
            },
        ]);

        $this->addAction([
            'index'  => 'download',
            'icon'   => 'icon-download',
            'title'  => 'Download Document',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.b2b.member-contracts.download', $row->id);
            },
        ]);
    }
}

