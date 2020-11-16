<?php

/*
 * This file is part of the Bizmuth Bot project
 *
 * (c) Antoine Bluchet <antoine@bluchet.fr>
 * (c) Lemay Marc <flugv1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\App\Http\Router;

use App\Drift\Controller\Twitch\PostCommand;
use App\Http\Router\Exception\RouteNotFoundException;
use App\Http\Router\RoutesCollection;
use App\Repository\Users as UsersRepository;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Serializer;

class RoutesCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            new  PostCommand(),
            new \App\Drift\Controller\User\PostCommand(
                new Serializer(),
                new UsersRepository(new Serializer(), sys_get_temp_dir())
            ),
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RoutesCollection::class);
    }

    public function it_is_a_question_of_whether_the_call_of_the_route_will_return_the_right_class()
    {
        $this->get('POST', '/twitch')->shouldReturnAnInstanceOf(PostCommand::class);
    }

    public function it_is_a_question_of_testing_a_route_that_does_not_exist()
    {
        $this->shouldThrow(RouteNotFoundException::class)->during('get', ['POST', '/toto']);
    }
}
