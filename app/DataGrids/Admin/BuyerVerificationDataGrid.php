<?php

namespace App\DataGrids\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class BuyerVerificationDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('buyer_verifications')
            ->leftJoin('customers', 'buyer_verifications.customer_id', '=', 'customers.id')
            ->select(
                'buyer_verifications.id',
                'buyer_verifications.company_name',
                'buyer_verifications.tax_id',
                'buyer_verifications.business_type',
                'buyer_verifications.status',
                'buyer_verifications.submitted_at',
                'buyer_verifications.reviewed_at',
                'customers.email',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as applicant_name")
            );

        $this->addFilter('id', 'buyer_verifications.id');
        $this->addFilter('company_name', 'buyer_verifications.company_name');
        $this->addFilter('tax_id', 'buyer_verifications.tax_id');
        $this->addFilter('status', 'buyer_verifications.status');
        $this->addFilter('email', 'customers.email');
        $this->addFilter('submitted_at', 'buyer_verifications.submitted_at');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
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
            'index'      => 'applicant_name',
            'label'      => 'Applicant Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => 'Email',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'company_name',
            'label'      => 'Company Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'business_type',
            'label'      => 'Business Type',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => 'Verification Status',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->status === 'approved') {
                    return '<span class="label-active">Approved</span>';
                }

                if ($row->status === 'pending') {
                    return '<span class="label-pending">Pending Review</span>';
                }

                return '<span class="label-canceled">Rejected</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'submitted_at',
            'label'      => 'Submitted At',
            'type'       => 'datetime',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'index'  => 'view',
            'icon'   => 'icon-eye',
            'title'  => 'Review Application',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.b2b.buyer_verifications.view', $row->id);
            },
        ]);
    }
}

