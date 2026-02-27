<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('password');
            $table->string('full_name', 200)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('department', 100)->nullable();
            $table->timestamp('last_notification_read_at')->nullable();
        });

        // Sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache tables
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Job tables
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Master tables
        Schema::create('master_departments', function (Blueprint $table) {
            $table->id();
            $table->string('dept_name');
            $table->timestamps();
        });

        Schema::create('master_cost_center', function (Blueprint $table) {
            $table->id();
            $table->string('cc_code', 50)->nullable();
            $table->string('cc_name', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('master_io', function (Blueprint $table) {
            $table->id();
            $table->string('io_number', 50)->nullable();
            $table->string('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('master_plants', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code', 50)->nullable();
            $table->string('plant_name', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('master_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 100)->nullable();
            $table->string('item_name')->nullable();
            $table->string('uom', 50)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('master_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->timestamps();
        });

        Schema::create('master_customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50)->nullable();
            $table->string('customer_name')->nullable();
            $table->timestamps();
        });

        Schema::create('master_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_no', 50)->unique();
            $table->string('asset_name')->nullable();
            $table->string('description')->nullable();
            $table->string('status', 20)->default('Active');
            $table->timestamps();
        });

        Schema::create('master_storage_locations', function (Blueprint $table) {
            $table->id();
            $table->string('sloc', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->string('status', 20)->default('Active');
            $table->timestamps();
        });

        Schema::create('master_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code')->unique();
            $table->string('vendor_name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('terms_of_payment')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        // Projects table
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code', 100)->nullable();
            $table->string('project_name')->nullable();
            $table->text('description')->nullable();
            $table->string('customer', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('year', 10)->nullable();
            $table->unsignedBigInteger('pic_user_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('die_go')->nullable();
            $table->date('to')->nullable();
            $table->date('pp1')->nullable();
            $table->date('pp2')->nullable();
            $table->date('pp3')->nullable();
            $table->date('mass_pro')->nullable();
            $table->string('status', 50)->nullable();
        });

        // Budget plans table (with all columns from subsequent migrations)
        Schema::create('budget_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('fiscal_year', 10)->nullable();
            $table->integer('start_year')->nullable();
            $table->integer('end_year')->nullable();
            $table->string('department', 100)->nullable();
            $table->string('io_number', 50)->nullable();
            $table->string('cc_code', 50)->nullable();
            $table->string('investment_type', 50)->nullable();
            $table->string('customer', 100)->nullable();
            $table->string('model')->nullable();
            $table->string('purpose', 100)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('status', 50)->default('Draft');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('total_budget', 15, 2)->nullable();
            $table->string('current_approver_role', 100)->nullable();
            $table->unsignedBigInteger('dept_head_id')->nullable();
            $table->timestamp('dept_head_approved_at')->nullable();
            $table->unsignedBigInteger('div_head_id')->nullable();
            $table->timestamp('div_head_approved_at')->nullable();
            $table->unsignedBigInteger('finance_id')->nullable();
            $table->timestamp('finance_approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Budget items table (with all columns from subsequent migrations)
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_item_id')->nullable();
            $table->string('item_code', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('io_id', 50)->nullable();
            $table->string('cc_id', 50)->nullable();
            $table->string('item_name')->nullable();
            $table->string('brand_spec', 255)->nullable();
            $table->string('fiscal_year', 10)->nullable();
            $table->decimal('qty', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('estimated_price', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('process', 255)->nullable();
            $table->string('application_process', 255)->nullable();
            $table->string('condition_status', 50)->nullable();
            $table->text('condition_notes')->nullable();
            $table->string('target_schedule', 50)->nullable();
            $table->text('evaluation_obstacle')->nullable();
            $table->text('evaluation_reason')->nullable();
        });

        // Purchase requests table (with all columns from subsequent migrations)
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('business_category', 100)->nullable();
            $table->string('periode', 50)->nullable();
            $table->string('io_number', 100)->nullable();
            $table->string('cost_center', 100)->nullable();
            $table->unsignedBigInteger('budget_item_id')->nullable();
            $table->string('budget_link', 255)->nullable();
            $table->string('item_code', 100)->nullable();
            $table->date('request_date')->nullable();
            $table->unsignedBigInteger('requester_id')->nullable();
            $table->decimal('qty_req', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->decimal('estimated_price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->string('status', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('purpose')->nullable();
            $table->string('asset_no', 50)->nullable();
            $table->string('gl_account', 50)->nullable();
            $table->string('storage_location', 50)->nullable();
            $table->string('plant', 100)->nullable();
            $table->string('pic', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('current_approver_role', 100)->nullable();
            $table->unsignedBigInteger('dept_head_id')->nullable();
            $table->timestamp('dept_head_approved_at')->nullable();
            $table->unsignedBigInteger('finance_id')->nullable();
            $table->timestamp('finance_approved_at')->nullable();
            $table->unsignedBigInteger('div_head_id')->nullable();
            $table->timestamp('div_head_approved_at')->nullable();
            $table->unsignedBigInteger('purchasing_id')->nullable();
            $table->timestamp('purchasing_executed_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // PR workflow history
        Schema::create('pr_workflow_history', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number');
            $table->string('action');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('actor_id');
            $table->timestamps();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Role permissions
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role')->unique();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // User customers pivot
        Schema::create('user_customers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('master_customer_id');
            $table->timestamps();
        });

        // Purchase orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->date('po_date');
            $table->unsignedBigInteger('vendor_id');
            $table->date('expected_delivery_date')->nullable();
            $table->string('status')->default('Draft');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by');
            $table->string('current_approver_role')->nullable();
            $table->string('plant')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('delivery_terms')->nullable();
            $table->timestamps();
        });

        // PO items
        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_id');
            $table->unsignedBigInteger('pr_item_id')->nullable();
            $table->string('item_description');
            $table->decimal('qty', 10, 2);
            $table->string('uom')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Budget transfers
        Schema::create('budget_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_item_id')->nullable();
            $table->string('item_name');
            $table->unsignedBigInteger('source_plan_id')->nullable();
            $table->string('source_io_number')->nullable();
            $table->string('source_project_name')->nullable();
            $table->unsignedBigInteger('target_plan_id')->nullable();
            $table->string('target_io_number')->nullable();
            $table->string('target_project_name')->nullable();
            $table->string('customer')->nullable();
            $table->string('business_category')->nullable();
            $table->string('fiscal_year', 4)->nullable();
            $table->text('reason');
            $table->string('berita_acara_path');
            $table->string('berita_acara_filename');
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_transfers');
        Schema::dropIfExists('po_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('user_customers');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('pr_workflow_history');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('budget_plans');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('master_vendors');
        Schema::dropIfExists('master_storage_locations');
        Schema::dropIfExists('master_assets');
        Schema::dropIfExists('master_customers');
        Schema::dropIfExists('master_categories');
        Schema::dropIfExists('master_items');
        Schema::dropIfExists('master_plants');
        Schema::dropIfExists('master_io');
        Schema::dropIfExists('master_cost_center');
        Schema::dropIfExists('master_departments');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
