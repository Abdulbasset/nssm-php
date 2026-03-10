<?php

use Abdulbasset\NssmPhp\Nssm;
use Abdulbasset\NssmPhp\NssmSet;
use Abdulbasset\NssmPhp\Startup;
use Abdulbasset\NssmPhp\Executors\FakeExecutor;
use Abdulbasset\NssmPhp\Status;

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
