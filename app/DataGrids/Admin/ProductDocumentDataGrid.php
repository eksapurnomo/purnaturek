<?php

namespace App\DataGrids\Admin;

use App\Models\ProductDocument;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ProductDocumentDataGrid extends DataGrid
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
        $queryBuilder = DB::table('product_documents')
            ->leftJoin('products', 'product_documents.product_id', '=', 'products.id')
            ->leftJoin('product_flat', function ($join) {
                $join->on('products.id', '=', 'product_flat.product_id')
                    ->where('product_flat.locale', '=', app()->getLocale())
                    ->where('product_flat.channel', '=', core()->getCurrentChannelCode());
            })
            ->select(
                'product_documents.id',
                'product_documents.product_id',
                'product_documents.title',
                'product_documents.document_type',
                'product_documents.description',
                'product_documents.original_filename',
                'product_documents.file_size',
                'product_documents.version',
                'product_documents.status',
                'product_documents.issue_date',
                'product_documents.expiry_date',
                'product_documents.created_at',
                'products.sku as product_sku',
                DB::raw('COALESCE(product_flat.name, products.sku) as product_name')
            );

        $this->addFilter('id', 'product_documents.id');
        $this->addFilter('title', 'product_documents.title');
        $this->addFilter('product_sku', 'products.sku');
        $this->addFilter('document_type', 'product_documents.document_type');
        $this->addFilter('status', 'product_documents.status');

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
            'index'      => 'title',
            'label'      => 'Document Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'product_name',
            'label'      => 'Associated Product',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return '<div class="flex flex-col"><span class="font-medium">' . e($row->product_name) . '</span><span class="text-xs text-gray-500">SKU: ' . e($row->product_sku) . '</span></div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'document_type',
            'label'      => 'Document Type',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $label = ProductDocument::DOCUMENT_TYPES[$row->document_type] ?? ucfirst($row->document_type);
                return '<span class="text-sm">' . e($label) . '</span>';
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
                return $row->version ? '<span class="font-mono text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800">' . e($row->version) . '</span>' : '—';
            },
        ]);

        $this->addColumn([
            'index'      => 'file_size',
            'label'      => 'File Size',
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                $bytes = (int) $row->file_size;
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2) . ' MB';
                }
                if ($bytes >= 1024) {
                    return number_format($bytes / 1024, 1) . ' KB';
                }
                return $bytes . ' B';
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
                if ($row->status === ProductDocument::STATUS_PUBLISHED) {
                    return '<span class="label-active">Published</span>';
                }

                return '<span class="label-pending">Draft</span>';
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

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'index'  => 'download',
            'icon'   => 'icon-download',
            'title'  => 'Download File',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.catalog.product_documents.download', $row->id);
            },
        ]);

        $this->addAction([
            'index'  => 'edit',
            'icon'   => 'icon-edit',
            'title'  => 'Edit Document',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.catalog.product_documents.edit', $row->id);
            },
        ]);

        $this->addAction([
            'index'  => 'delete',
            'icon'   => 'icon-delete',
            'title'  => 'Delete Document',
            'method' => 'POST',
            'url'    => function ($row) {
                return route('admin.catalog.product_documents.delete', $row->id);
            },
        ]);
    }
}

