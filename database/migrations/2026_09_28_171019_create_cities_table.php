<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول المدن.
     *
     * ملاحظة:
     * التوثيق يذكر جدول cities ولكنه لا يحدد حقوله تفصيلياً.
     * هذه الحقول هي تصميم تنفيذي لتغطية البحث والرحلات.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name_ar', 100);

            $table->string('name_en', 100)->nullable();

            $table->string('country_code', 2);

            $table
                ->boolean('is_active')
                ->default(true)
                ->index();

            $table
                ->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->unique([
                'name_ar',
                'country_code',
            ]);
        });
    }

    /**
     * حذف جدول المدن.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
