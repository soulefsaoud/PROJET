<?php
// tests/Controller/MenuControllerTest.php
namespace App\Tests\Controller;

use App\Entity\Menu;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MenuControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/menu');
        $this->assertResponseIsSuccessful();
    }

    public function testNew(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/menu/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $form['menu_form[nom]'] = 'Test Menu';
        $form['menu_form[description]'] = 'Test Description';
        $form['menu_form[date_creation]'] = date('Y-m-d H:i:s');

        $client->submit($form);

        $this->assertResponseRedirects('/menu');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        $menu = new Menu();
        $menu->setNom('Test Menu');
        $menu->setDescription('Test Description');
        $menu->setDateCreation(new \DateTime('now'));

        $entityManager->persist($menu);
        $entityManager->flush();

        $client->request('GET', '/menu/' . $menu->getId());
        $this->assertResponseIsSuccessful();

        $entityManager->remove($menu);
        $entityManager->flush();
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        $menu = new Menu();
        $menu->setNom('Test Menu');
        $menu->setDescription('Test Description');
        $menu->setDateCreation(new \DateTime('now'));

        $entityManager->persist($menu);
        $entityManager->flush();

        $crawler = $client->request('GET', '/menu/' . $menu->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Update')->form();
        $form['menu_form[nom]'] = 'Updated Test Menu';
        $client->submit($form);

        $this->assertResponseRedirects('/menu');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $menu = $entityManager->getRepository(Menu::class)->find($menu->getId());
        $entityManager->remove($menu);
        $entityManager->flush();
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        $menu = new Menu();
        $menu->setNom('Test Menu');
        $menu->setDescription('Test Description');
        $menu->setDateCreation(new \DateTime('now'));

        $entityManager->persist($menu);
        $entityManager->flush();

        $crawler = $client->request('GET', '/menu/' . $menu->getId());
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/menu/' . $menu->getId(), [
            '_token' => $token,
            '_method' => 'DELETE',
        ]);

        $this->assertResponseRedirects('/menu');
    }
}
