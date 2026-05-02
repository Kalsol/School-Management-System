<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExcuseAndAdminTrackingToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // e.g., 'Medical', 'Family Emergency'
            $table->string('excuse_type')->nullable()->after('remarks');
            // File path for uploaded documents/images
            $table->string('evidence')->nullable()->after('excuse_type');
            // The text feedback from the admin to the parent
            $table->text('admin_response')->nullable()->after('evidence');
            // The ID of the staff member who approved/rejected
            $table->unsignedBigInteger('admin_id')->nullable()->after('admin_response');
            // When the decision was made
            $table->timestamp('handled_at')->nullable()->after('admin_id');
            // Foreign key for admin (optional but recommended)
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['excuse_type', 'evidence', 'admin_response', 'admin_id', 'handled_at']);
        });
    }
}
