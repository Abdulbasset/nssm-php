<?php

use Abdulbasset\NssmPhp\Nssm;
use Abdulbasset\NssmPhp\Status;
use Abdulbasset\NssmPhp\NssmSet;
use Abdulbasset\NssmPhp\Startup;
use Abdulbasset\NssmPhp\NssmRotation;
use Abdulbasset\NssmPhp\Executors\FakeExecutor;

beforeEach(function () {
    $this->executor = new FakeExecutor('nssm');
    $this->nssm = new Nssm(service: 'MyService', executor: $this->executor);
});

test('it can set the service name', function () {
    $this->nssm->service('NewService');
    $this->nssm->start();

    expect($this->executor->commands[0])->toBe(['nssm', 'start', 'NewService']);
});

test('it can set the nssm binary path', function () {
    $executor = new FakeExecutor('C:\path\to\nssm.exe');
    $nssm = new Nssm(service: 'MyService', nssm: 'C:\path\to\nssm.exe', executor: $executor);

    $nssm->start();

    expect($executor->commands[0])->toBe(['C:\path\to\nssm.exe', 'start', 'MyService']);
});

test('it can install a service', function () {
    $this->nssm->bin('C:\php\php.exe')->install('artisan', 'queue:work');

    expect($this->executor->commands[0])->toBe(['nssm', 'install', 'MyService', 'C:\php\php.exe', 'artisan', 'queue:work']);
});

test('it can remove a service', function () {
    $this->nssm->remove();

    expect($this->executor->commands[0])->toBe(['nssm', 'remove', 'MyService', 'confirm']);
});

test('it can start a service', function () {
    $this->nssm->start();

    expect($this->executor->commands[0])->toBe(['nssm', 'start', 'MyService']);
});

test('it can stop a service', function () {
    $this->nssm->stop();

    expect($this->executor->commands[0])->toBe(['nssm', 'stop', 'MyService']);
});

test('it can restart a service', function () {
    $this->nssm->restart();

    expect($this->executor->commands[0])->toBe(['nssm', 'restart', 'MyService']);
});

test('it can get the status of a service', function () {
    // Tell the fake executor to simulate a running Windows service
    $this->executor->returnFor('status MyService', 'SERVICE_RUNNING');

    $status = $this->nssm->status();

    expect($status->running())->toBeTrue();
    expect($this->executor->commands[0])->toBe(['nssm', 'status', 'MyService']);
});

test('it maps raw nssm output to the correct Status enum', function (string $rawOutput, Status $expectedStatus) {
    $this->executor->returnFor('status MyService', $rawOutput);

    $status = $this->nssm->status();

    expect($status)->toBe($expectedStatus);
})->with([
    ['SERVICE_RUNNING', Status::Running],
    ['SERVICE_STOPPED', Status::Stopped],
    ['SERVICE_START_PENDING', Status::StartPending],
    ['SERVICE_STOP_PENDING', Status::StopPending],
    ['SERVICE_PAUSED', Status::Paused],
    ['SERVICE_NOT_FOUND', Status::NotFound],
]);

test('it can set service parameters using a callback', function () {
    $this->nssm->set(
        fn(NssmSet $set) => $set
            ->displayName('My Custom Service')
            ->description('This is a test service')
            ->startup(Startup::Automatic)
            ->appDirectory('C:\www\app')
            ->output('C:\www\app\logs\out.log')
            ->error('C:\www\app\logs\error.log')
    );

    expect($this->executor->commands)
        ->toHaveCount(6)
        ->and($this->executor->commands[0])->toBe(['nssm', 'set', 'MyService', 'DisplayName', 'My Custom Service'])
        ->and($this->executor->commands[1])->toBe(['nssm', 'set', 'MyService', 'Description', 'This is a test service'])
        ->and($this->executor->commands[2])->toBe(['nssm', 'set', 'MyService', 'Start', 'SERVICE_AUTO_START'])
        ->and($this->executor->commands[3])->toBe(['nssm', 'set', 'MyService', 'AppDirectory', 'C:\www\app'])
        ->and($this->executor->commands[4])->toBe(['nssm', 'set', 'MyService', 'AppStdout', 'C:\www\app\logs\out.log'])
        ->and($this->executor->commands[5])->toBe(['nssm', 'set', 'MyService', 'AppStderr', 'C:\www\app\logs\error.log']);
});

test('can set rotation on service start/restart', function () {
    $this->nssm->set(function (NssmSet $set) {
        $set->rotation();
    });
    expect($this->executor->commands)
        ->and($this->executor->commands[0])->toBe(['nssm', 'set', 'MyService', 'AppRotateFiles', '1']);
});

test('can set rotation while the service is running each 1 minute', function () {
    $this->nssm->set(function (NssmSet $set) {
        $set->rotation(function (NssmRotation $rotation) {
            $rotation->everySeconds('60');
        });
    });
    expect($this->executor->commands)
        ->and($this->executor->commands[0])->toBe(['nssm', 'set', 'MyService', 'AppRotateFiles', '1'], 'auto enable')
        ->and($this->executor->commands[1])->toBe(['nssm', 'set', 'MyService', 'AppRotateSeconds', 60]);
});

test('can set rotation while the service is running each 1 Hour using interval', function () {
    $this->nssm->set(function (NssmSet $set) {
        $set->rotation(function (NssmRotation $rotation) {
            $rotation->everySeconds(new \DateInterval('PT1H'));
        });
    });

    expect($this->executor->commands)
        ->and($this->executor->commands[0])->toBe(['nssm', 'set', 'MyService', 'AppRotateFiles', '1'], 'auto enable')
        ->and($this->executor->commands[1])->toBe(['nssm', 'set', 'MyService', 'AppRotateSeconds', 60 * 60]);
});

test('can set rotation while the service is running if file exceed 1 megabyte', function () {
    $this->nssm->set(function (NssmSet $set) {
        $set->rotation(function (NssmRotation $rotation) {
            $rotation->everyBytes(1024 * 1024);
        });
    });

    expect($this->executor->commands)
        ->and($this->executor->commands[0])->toBe(['nssm', 'set', 'MyService', 'AppRotateFiles', '1'], 'auto enable')
        ->and($this->executor->commands[1])->toBe(['nssm', 'set', 'MyService', 'AppRotateBytes', 1024 * 1024]);
});

test('can reset the rotation to default', function () {
    $this->nssm->set(function (NssmSet $set) {
        $set->rotation(function (NssmRotation $rotation) {
            $rotation->reset();
        });
    });

    expect($this->executor->commands)
        ->toHaveCount(5)
        ->and($this->executor->commands[1])->toBe(['nssm', 'reset', 'MyService', 'AppRotateFiles'])
        ->and($this->executor->commands[2])->toBe(['nssm', 'reset', 'MyService', 'AppRotateOnline'])
        ->and($this->executor->commands[3])->toBe(['nssm', 'reset', 'MyService', 'AppRotateSeconds'])
        ->and($this->executor->commands[4])->toBe(['nssm', 'reset', 'MyService', 'AppRotateBytes']);
});
