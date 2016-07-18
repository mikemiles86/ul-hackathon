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
        $count = $taskRunner->updateContentDocuments(30);

        return $this->render('default/task-runner.html.twig', [
            'title' => 'Update Content Documents',
            'count' => $count,
            'messages' => $taskRunner->getMessages('update_content', true),
            'errors' => $taskRunner->getErrorMessages('update_content', true),
            'time_spent' => $taskRunner->timeSpent('update_content'),
        ]);
    }

    /**
     * @Route("/build-sitemap", name="Build Sitemaps")
     */
    public function buildSitemapAction(Request $request) {
        $taskRunner = new ULTaskRunner($this->container->get('app.uldatabase'));

        $count = $taskRunner->buildSitemaps(30);
        return $this->render('default/task-runner.html.twig', [
          'title' => 'Build Sitemaps',
          'count' => $count,
          'messages' => $taskRunner->getMessages('build_sitemaps'),
          'errors' => $taskRunner->getErrorMessages('build_sitemaps'),
          'time_spent' => $taskRunner->timeSpent('build_sitemaps'),
        ]);
    }

    /**
     * @Route("/parse-sitemap", name="ParseSitemap")
     */
    public function parseSitemapAction(Request $request) {
        $taskRunner = new ULTaskRunner($this->container->get('app.uldatabase'));

        $count = $taskRunner->parseSitemap(30);

        return $this->render('default/task-runner.html.twig', [
          'title' => 'Parse Sitemap',
          'count' => $count,
          'messages' => $taskRunner->getMessages('parse_sitemap'),
          'errors' => $taskRunner->getErrorMessages('parse_sitemap'),
          'time_spent' => $taskRunner->timeSpent('parse_sitemap'),
        ]);
    }

    /**
     * @Route("/all-tasks", name="All Tasks")
     */
    public function allTasksAction(Request $request) {
        $taskRunner = new ULTaskRunner($this->container->get('app.uldatabase'));

        $tasks = array(
          'buildSitemaps',
          'parseSitemap',
          'updateContentDocuments'
        );

        $count = $taskRunner->runMultipleTasks($tasks, 60);

        return $this->render('default/task-runner.html.twig', [
          'title' => 'All Tasks',
          'count' => $count,
          'messages' => $taskRunner->getMessages(),
          'errors' => $taskRunner->getErrorMessages(),
          'time_spent' => $taskRunner->timeSpent('multiple_tasks'),
        ]);
    }

}
