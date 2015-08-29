<?php

namespace spec\Pim\Bundle\NotificationBundle\Update;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestDataCollectorSpec extends ObjectBehavior
{
    function let(RequestStack $stack)
    {
        $this->beConstructedWith($stack);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\RequestDataCollector');
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\DataCollectorInterface');
    }

    function it_collects_data_from_request($stack, Request $request)
    {
        $stack->getCurrentRequest()->willReturn($request);
        $request->getHost()->willReturn('http://demo.akeneo.com/');
        $this->collect()->shouldReturn(['pim_host' => 'http://demo.akeneo.com/']);
    }
}
