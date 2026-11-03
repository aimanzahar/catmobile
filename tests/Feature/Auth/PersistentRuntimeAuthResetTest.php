<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Native\Mobile\Runtime;
use ReflectionProperty;
use Tests\TestCase;

class PersistentRuntimeAuthResetTest extends TestCase
{
    public function test_runtime_reset_drops_the_previous_requests_user(): void
    {
        $runtimeApp = new ReflectionProperty(Runtime::class, 'app');
        $runtimeApp->setValue(null, $this->app);

        try {
            $guard = $this->app['auth']->guard();
            $guard->setUser(User::fromRecord(['id' => 'u1', 'name' => 'Stale Name']));

            Runtime::reset();

            $this->assertNotSame($guard, $this->app['auth']->guard());
            $this->assertFalse($this->app['auth']->guard()->hasUser());
        } finally {
            $runtimeApp->setValue(null, null);
        }
    }
}
