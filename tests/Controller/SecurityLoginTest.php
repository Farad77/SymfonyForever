<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SecurityLoginTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConfiguration()->setUseSchemaEditorApi(true);
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    #[DataProvider('usernameCases')]
    public function testLoginIgnoresUsernameCaseAndKeepsSession(string $username): void
    {
        $this->createUser('PseudoMixte', 'secret123');

        $this->login($username, 'secret123');
        self::assertResponseRedirects('/characters');
        $this->client->request('GET', '/characters');
        self::assertResponseIsSuccessful();
    }

    public static function usernameCases(): iterable
    {
        yield 'original' => ['PseudoMixte'];
        yield 'minuscules' => ['pseudomixte'];
        yield 'majuscules' => ['PSEUDOMIXTE'];
        yield 'mixte' => ['pSeUdOmIxTe'];
    }

    public function testLoginStillRejectsWrongPassword(): void
    {
        $this->createUser('PseudoMixte', 'secret123');

        $this->login('pseudomixte', 'wrong-password');
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.flash-error', 'Identifiants incorrects');
    }

    public function testRegistrationRejectsUsernameWithDifferentCase(): void
    {
        $this->createUser('PseudoMixte', 'secret123');

        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'username' => 'pseudomixte',
            'email' => 'another@example.com',
            'password' => 'secret123',
        ]);
        self::assertSelectorTextContains('.flash-error', 'déjà utilisé');
    }

    public function testLoginRejectsAmbiguousExistingUsernames(): void
    {
        $this->createUser('PseudoMixte', 'secret123');
        $this->createUser('pseudomixte', 'secret123', 'another@example.com');

        $this->login('PSEUDOMIXTE', 'secret123');
        self::assertResponseRedirects('/login');
    }

    private function createUser(string $username, string $password, string $email = 'player@example.com'): void
    {
        $user = (new User())->setUsername($username)->setEmail($email);
        $user->setPassword(self::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();
    }

    private function login(string $username, string $password): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Entrer sur l\'Île', [
            '_username' => $username,
            '_password' => $password,
        ]);
    }
}
