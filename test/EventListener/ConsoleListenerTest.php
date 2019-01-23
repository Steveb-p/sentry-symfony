<?php

namespace Sentry\SentryBundle\Test\EventListener;

use Sentry\SentryBundle\EventListener\ConsoleListener;
use Sentry\State\Hub;
use Sentry\State\HubInterface;
use Sentry\State\Scope;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class ConsoleListenerTest extends \PHPUnit\Framework\TestCase
{
    private $currentHub;
    private $currentScope;

    protected function setUp()
    {
        parent::setUp();

        $this->currentScope = new Scope();
        $this->currentHub = $this->prophesize(HubInterface::class);
        $this->currentHub->getScope()
            ->shouldBeCalled()
            ->willReturn($this->currentScope);

        Hub::setCurrent($this->currentHub->reveal());
    }

    public function testOnConsoleCommandAddsCommandName(): void
    {
        $command = $this->prophesize(Command::class);
        $command->getName()
            ->willReturn('sf:command:name');

        $event = $this->prophesize(ConsoleCommandEvent::class);
        $event->getCommand()
            ->willReturn($command->reveal());

        $listener = new ConsoleListener($this->currentHub->reveal());

        $listener->onConsoleCommand($event->reveal());

        $this->assertSame(['command' => 'sf:command:name'], $this->currentScope->getTags());
    }

    public function testOnConsoleCommandAddsPlaceholderCommandName(): void
    {
        $event = $this->prophesize(ConsoleCommandEvent::class);
        $event->getCommand()
            ->willReturn(null);

        $listener = new ConsoleListener($this->currentHub->reveal());

        $listener->onConsoleCommand($event->reveal());

        $this->assertSame(['command' => 'N/A'], $this->currentScope->getTags());
    }
}
