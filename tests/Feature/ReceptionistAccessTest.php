<?php

namespace Tests\Feature;

use App\Http\Middleware\RestrictReceptionistAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ReceptionistAccessTest extends TestCase
{
    public function test_receptionist_can_use_every_schedule_and_attendance_route(): void
    {
        $allowedRoutes = [
            'schedule.index', 'schedule.create', 'schedule.search', 'schedule.store',
            'schedule.show', 'schedule.edit', 'schedule.update', 'schedule.destroy',
            'schedule.cloneWeek', 'schedule.pdf',
            'attendance.index', 'attendance.search', 'attendance.register',
            'attendance.attendanceSchedule', 'attendance.searchSchedule',
        ];

        foreach ($allowedRoutes as $routeName) {
            $response = app(RestrictReceptionistAccess::class)->handle(
                $this->requestFor($routeName, $this->user('RECEPCIONIST')),
                fn () => response('permitido')
            );

            $this->assertSame(200, $response->getStatusCode(), "La ruta {$routeName} debe estar permitida.");
        }
    }

    public function test_receptionist_cannot_access_other_modules_directly(): void
    {
        $forbiddenRoutes = [
            'patients.index', 'supplies.index', 'edit.index', 'users.index',
            'medicines.index', 'machines.index', 'print.index',
            'attendance.list', 'attendance.asigne', 'treatment.index',
        ];

        foreach ($forbiddenRoutes as $routeName) {
            try {
                app(RestrictReceptionistAccess::class)->handle(
                    $this->requestFor($routeName, $this->user('RECEPCIONIST')),
                    fn () => response('no debe alcanzarse')
                );
                $this->fail("La ruta {$routeName} no fue bloqueada.");
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }
    }

    public function test_receptionist_menu_only_shows_schedule_and_attendance(): void
    {
        Auth::setUser($this->user('RECEPCIONIST'));
        $html = view('layouts.app')->render();

        $this->assertStringContainsString('Horario de paciente', $html);
        $this->assertStringContainsString('Asistencia', $html);
        $this->assertStringNotContainsString('Asignación', $html);
        $this->assertStringNotContainsString('Tratamiento', $html);
        $this->assertStringNotContainsString('Administración', $html);
        $this->assertStringNotContainsString('Impresión', $html);
    }

    public function test_receptionist_scope_does_not_change_another_role(): void
    {
        $response = app(RestrictReceptionistAccess::class)->handle(
            $this->requestFor('treatment.index', $this->user('NURSE')),
            fn () => response('permitido')
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    private function requestFor(string $routeName, User $user): Request
    {
        $request = Request::create('/prueba-permiso', 'GET');
        $route = new Route(['GET'], '/prueba-permiso', fn () => null);
        $route->name($routeName);
        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function user(string $position): User
    {
        return (new User)->forceFill([
            'id' => 1,
            'name' => 'Usuario',
            'last_name_one' => 'Prueba',
            'position' => $position,
            'email' => strtolower($position).'@example.test',
        ]);
    }
}
