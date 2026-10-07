<?php

namespace Tests\Feature;

use App\Models\Supply;
use App\Models\SupplyOrder;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplyEmergencyRequisitionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        $this->createSchema();
        Carbon::setTestNow('2026-10-07 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_calculation_only_adds_emergencies_attended_after_last_requisition(): void
    {
        $user = $this->user();
        SupplyOrder::create([
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-03',
            'generated_at' => '2026-10-04 09:00:00',
            'emergency_to' => '2026-10-04 09:00:00',
        ]);
        $this->emergencySession(1, '2026-10-04', '2026-10-04 08:00:00');
        $this->emergencySession(2, '2026-10-05', '2026-10-05 10:00:00');
        Supply::create([
            'material' => 'Insumo para fístula',
            'type' => 'supply',
            'for_vascular_access' => 'fistula',
            'units_per_patient' => 1.5,
            'existencias' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('supplies.calculate', [
            'start_date' => '2026-10-08',
            'end_date' => '2026-10-10',
        ]));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('emergencyCount'));
        $this->assertSame(1.5, $response->viewData('supplies')->first()->requested_quantity);
    }

    public function test_generation_saves_snapshot_and_reprint_does_not_create_another_order(): void
    {
        $user = $this->user();
        SupplyOrder::create([
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-03',
            'generated_at' => '2026-10-04 09:00:00',
            'emergency_to' => '2026-10-04 09:00:00',
        ]);
        $this->emergencySession(3, '2026-10-06', '2026-10-06 11:00:00');
        Supply::create([
            'material' => 'Filtro Elisio',
            'type' => 'filter',
            'for_vascular_access' => 'no_apply',
            'units_per_patient' => 2.5,
            'existencias' => 0,
        ]);

        $this->actingAs($user)->post(route('supplies.pdf'), [
            'start_date' => '2026-10-08',
            'end_date' => '2026-10-10',
        ])->assertOk();

        $order = SupplyOrder::latest('id')->firstOrFail();
        $this->assertSame(1, $order->emergency_count);
        $this->assertDatabaseHas('supply_order_items', [
            'supply_order_id' => $order->id,
            'material' => 'Filtro Elisio',
            'requested_quantity' => 2.5,
        ]);
        $orderCount = SupplyOrder::count();

        $this->actingAs($user)->get(route('supplies.reprint', $order))->assertOk();

        $this->assertSame($orderCount, SupplyOrder::count());
    }

    private function emergencySession(int $patientId, string $date, string $assignedAt): void
    {
        $activePatientId = DB::table('active_patient')->insertGetId([
            'patient_id' => $patientId,
            'date' => $date,
            'active' => false,
            'created_at' => $assignedAt,
            'updated_at' => $assignedAt,
        ]);
        DB::table('schedules_patient')->insert([
            'schedules_id' => 5,
            'patient_id' => $patientId,
            'date' => $date,
            'machine_id' => 1,
            'created_at' => $assignedAt,
            'updated_at' => $assignedAt,
        ]);
        DB::table('nurse_patient')->insert([
            'active_patient_id' => $activePatientId,
            'user_id' => 1,
            'date' => $date,
            'history' => false,
            'created_at' => $assignedAt,
            'updated_at' => $assignedAt,
        ]);
        DB::table('dialysis_monitoring')->insert([
            'patient_id' => $patientId,
            'vascular_access' => 'fistula',
            'history' => true,
            'created_at' => $assignedAt,
            'updated_at' => $assignedAt,
        ]);
        DB::table('dialysis_prescription')->insert([
            'patient_id' => $patientId,
            'type_dialyzer' => 'F6ELISIO21H',
            'history' => true,
            'created_at' => $assignedAt,
            'updated_at' => $assignedAt,
        ]);
    }

    private function user(): User
    {
        return User::forceCreate([
            'name' => 'Root',
            'position' => 'ROOT',
            'email' => 'root@example.test',
            'password' => 'password',
            'enabled' => true,
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('material');
            $table->string('type');
            $table->string('for_vascular_access');
            $table->decimal('units_per_patient', 10, 2)->default(1);
            $table->decimal('existencias', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('supply_orders', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('generated_at');
            $table->timestamp('emergency_from')->nullable();
            $table->timestamp('emergency_to')->nullable();
            $table->unsignedInteger('emergency_count')->default(0);
            $table->timestamps();
        });
        Schema::create('supply_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supply_order_id');
            $table->unsignedBigInteger('supply_id')->nullable();
            $table->string('material');
            $table->decimal('requested_quantity', 12, 2)->default(0);
            $table->decimal('cancellation_quantity', 12, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('active_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->date('date');
            $table->boolean('active');
            $table->timestamps();
        });
        Schema::create('schedules_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedules_id');
            $table->unsignedBigInteger('patient_id');
            $table->date('date');
            $table->unsignedBigInteger('machine_id');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('nurse_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_patient_id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->boolean('history')->default(false);
            $table->timestamps();
        });
        Schema::create('dialysis_monitoring', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('vascular_access')->nullable();
            $table->boolean('history')->default(false);
            $table->timestamps();
        });
        Schema::create('dialysis_prescription', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('type_dialyzer')->nullable();
            $table->boolean('history')->default(false);
            $table->timestamps();
        });
    }
}
