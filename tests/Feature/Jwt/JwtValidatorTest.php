<?php

namespace Tests\Feature\Jwt;

use App\Jwt\Builder;
use App\Jwt\Generator;
use App\Jwt\Validator;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JwtValidatorTest extends TestCase
{
     public function testValidateNullSignature(): void
     {
         $token = Builder::build()->getToken();

         $this->assertFalse(Validator::validate($token));
     }

     public function testValidateInvalidSignature(): void
     {
         $token = Builder::build()->getToken();

         $token->setUserSignature('string');

         $this->assertFalse(Validator::validate($token));
     }

     public function testValidateHeaders(): void
     {
         $token = Builder::build()
             ->withHeader('typ', 'string')
             ->getToken();

         $token->setUserSignature(Generator::signature($token));

         $this->assertFalse(Validator::validate($token));
     }

     public function testValidateZeroExpiration(): void
     {
         $token = Builder::build()->getToken();

         $token->setUserSignature(Generator::signature($token));

         $this->assertFalse(Validator::validate($token));
     }

     public function testValidateExpiration(): void
     {
         $previousDay = (int) Carbon::now()->subDay()->timestamp;
         $token = Builder::build()
             ->expiresAt($previousDay)
             ->getToken();

         $token->setUserSignature(Generator::signature($token));

         $this->assertFalse(Validator::validate($token));
     }

     public function testValidateNonExistentUser(): void
     {
         $previousDay = Carbon::now()->addDay()->unix();
         $token = Builder::build()
             ->expiresAt($previousDay)
             ->withClaim('sub', 123)
             ->getToken();

         $token->setUserSignature(Generator::signature($token));

         $this->assertFalse(Validator::validate($token));
     }

    public function testValidateToken(): void
    {
        $user = User::factory()->create();
        $previousDay = (int) Carbon::now()->addDay()->timestamp;

        $token = Builder::build()
            ->expiresAt($previousDay)
            ->subject($user)
            ->getToken();

        $token->setUserSignature(Generator::signature($token));

        $this->assertTrue(Validator::validate($token));
    }
}
