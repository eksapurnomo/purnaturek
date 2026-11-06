<?php

namespace App\DataGrids\Admin;

use App\Models\MemberConversation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class MemberConversationDataGrid extends DataGrid
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
        $queryBuilder = DB::table('member_conversations')
            ->leftJoin('customers', 'member_conversations.customer_id', '=', 'customers.id')
            ->select(
                'member_conversations.id',
                'member_conversations.subject',
                'member_conversations.category',
                'member_conversations.status',
                'member_conversations.unread_for_admin_count',
                'member_conversations.last_message_at',
                'member_conversations.created_at',
                'customers.email as customer_email',
                DB::raw("TRIM(CONCAT(COALESCE(customers.first_name, ''), ' ', COALESCE(customers.last_name, ''))) as customer_name")
            );

        $this->addFilter('id', 'member_conversations.id');
        $this->addFilter('subject', 'member_conversations.subject');
        $this->addFilter('category', 'member_conversations.category');
        $this->addFilter('status', 'member_conversations.status');
        $this->addFilter('customer_email', 'customers.email');

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
            'index'      => 'subject',
            'label'      => 'Subject',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $unread = (int) $row->unread_for_admin_count;
                $badge = $unread > 0
                    ? ' <span class="rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 ml-1">' . $unread . ' new</span>'
                    : '';

                return '<div class="flex items-center"><span class="font-semibold">' . e($row->subject) . '</span>' . $badge . '</div>';
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
                $name = $row->customer_name ?: 'Member';
                return '<div class="flex flex-col"><span class="font-medium">' . e($name) . '</span><span class="text-xs text-gray-500">' . e($row->customer_email) . '</span></div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'category',
            'label'      => 'Category',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $label = MemberConversation::CATEGORIES[$row->category] ?? ucfirst(str_replace('_', ' ', $row->category));
                return '<span class="text-xs font-medium px-2 py-1 rounded bg-gray-100 dark:bg-gray-800">' . e($label) . '</span>';
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
                if ($row->status === MemberConversation::STATUS_WAITING_ADMIN) {
                    return '<span class="label-pending">Waiting on Admin</span>';
                }

                if ($row->status === MemberConversation::STATUS_WAITING_MEMBER) {
                    return '<span class="label-active">Waiting on Member</span>';
                }

                return '<span class="label-canceled">Closed</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'last_message_at',
            'label'      => 'Last Message',
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => false,
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
            'title'  => 'Open Conversation',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.b2b.conversations.view', $row->id);
            },
        ]);
    }
}

