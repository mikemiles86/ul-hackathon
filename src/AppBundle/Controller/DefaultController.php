<?php

namespace AppBundle\Controller;

use AppBundle\Util\ULDatabase;
use AppBundle\Util\ULParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Util\ULTaskRunner;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/update-content", name="Update Content")
     */
    public function updateContentAction(Request $request) {

        $taskRunner = new ULTaskRunner($this->container->get('app.uldatabase'));

        $updated = $taskRunner->updateContentDocuments(30);

        $response = 'Updated ' . $updated. ' Content Document(s) in ' . $taskRunner->timeSpent('update_content') . ' seconds';

        return new Response($response);

    }

    /**
     * @Route("/build-sitemap", name="Build Sitemaps")
     */
    public function buildSitemapAction(Request $request) {
        $taskRunner = new ULTaskRunner($this->container->get('app.uldatabase'));

        $built = $taskRunner->buildSitemaps(30);

        $response = 'Built ' . $built['sitemaps'] . ' Sitemap(s), with ' . $built['links'] . ' Links(s) in ' . $taskRunner->timeSpent('build_sitemaps') . ' seconds';

        return new Response($response);

    }

    /**
     * @Route("/parse-sitemap", name="ParseSitemap")
     */
    public function parseSitemapAction(Request $request) {
        $taskRunner = new ULTaskRunner($this->container->get('app.uldatabase'));
        $response = '';

        $updated = $taskRunner->parseSitemap(30);

        if ($errors = $taskRunner->getErrorMessage('parse_sitemap')) {
            $response .= 'The following errors occured:<ul><li>' . implode('</li><li>', $errors). '</li></ul>';
        }
        else {
            $response .= 'Sitemap parsed for ' . $updated;
        }

        return new Response($response);
    }


}
