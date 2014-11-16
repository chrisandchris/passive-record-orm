<?php
namespace Klit\Common\RowMapperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @name WelcomeController
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class WelcomeController extends Controller {

    public function indexAction() {
        return $this->render('KlitCommonRowMapperBundle::index.html.twig');
    }
}
