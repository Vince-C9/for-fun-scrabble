<?php
namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use App\Services\ScrabbleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrabbleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ScrabbleService $scrabbleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scrabbleService = app(ScrabbleService::class);
    }

    public function test_it_can_make_a_game(): void
    {
        $users = User::factory(1)->create();

        $this->scrabbleService->createGame($users);

        $this->assertDatabaseCount('games', 1);
    }
}
