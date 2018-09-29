<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use <?= $entity_full_class_name; ?>;
use <?= $form_full_class_name; ?>;
use <?= $form_search_full_class_name; ?>;
use App\Utils\Util;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Doctrine\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("<?= $route_path; ?>", name="<?= $route_name; ?>_")
 */
class <?= $class_name ?> extends Controller
{

    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
      $this->twig = $twig;   
      $this->twig->addGlobal('menuActual', "<?=$entity_var_singular?>");
    }

    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function index(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $<?= $entity_var_plural; ?> = $em->createQueryBuilder()->from(<?= $entity_class_name; ?>::class, '<?= $shortEntity; ?>')->select('<?= $shortEntity; ?>');

        $searchForm = $this->createForm(<?= $form_search_class_name; ?>::class);   
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            
        }

         if($request->query->get("sort")){
            $<?= $entity_var_plural; ?>->orderBy($request->query->get("sort"), $request->query->get("direction"));
         }else{
            $<?= $entity_var_plural; ?>->orderBy('<?= $shortEntity; ?>.<?= $entity_identifier; ?>', 'DESC');
        }

        $page      = ($request->query->getInt('page', 1) - 1) < 1 ? 1 : $request->query->getInt('page');
        $limit     = $this->container->getParameter("pagination")["limit"];

         $<?= $entity_var_plural; ?>->getQuery();
         $<?= $entity_var_plural; ?>->setFirstResult($limit * ($page - 1));
         $<?= $entity_var_plural; ?>->setMaxResults($limit);

        $paginator = new Paginator($<?= $entity_var_plural; ?>);
        $paginator->setParameters($request,$this->container);


        return $this->render('<?= $route_name; ?>/index.html.twig', [
            'title'         => "Listado de <?= $pluralName; ?>",
            'pagination'    => $paginator,
            'searchForm'    => $searchForm->createView()
        ]);

    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function new(Request $request)
    {
        $<?= $entity_var_singular; ?> = new <?= $entity_class_name; ?>();
        $form = $this->createForm(<?= $form_class_name; ?>::class, $<?= $entity_var_singular; ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($<?= $entity_var_singular; ?>);
            $em->flush();
            $this->addFlash('notice', '<?= $singularName; ?> Creada Correctamente');
            return $this->redirectToRoute('<?= $route_name; ?>_edit', ['<?= $entity_identifier; ?>' => $<?= $entity_var_singular; ?>->get<?= ucfirst($entity_identifier); ?>()]);
        }

        return $this->render('<?= $route_name; ?>/new.html.twig', [
            '<?= $entity_var_singular; ?>' => $<?= $entity_var_singular; ?>,
            'title' => "Nueva <?= $singularName; ?>",
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @Route("/{<?= $entity_identifier; ?>}", name="show", methods={"GET"})
     */
    public function show(<?= $entity_class_name; ?> $<?= $entity_var_singular; ?>)
    {
        return $this->render('<?= $route_name; ?>/show.html.twig', [
            'title' => "Mostrar <?= $singularName; ?>",
            '<?= $entity_var_singular; ?>' => $<?= $entity_var_singular; ?>,
        ]);
    }

    /**
     * @Route("/{<?= $entity_identifier; ?>}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, <?= $entity_class_name; ?> $<?= $entity_var_singular; ?>)
    {
        $form = $this->createForm(<?= $form_class_name; ?>::class, $<?= $entity_var_singular; ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '<?= $singularName; ?> Actualizada Correctamente');
            return $this->redirectToRoute('<?= $route_name; ?>_edit', ['<?= $entity_identifier; ?>' => $<?= $entity_var_singular; ?>->get<?= ucfirst($entity_identifier); ?>()]);
        }

        return $this->render('<?= $route_name; ?>/edit.html.twig', [
            '<?= $entity_var_singular; ?>' => $<?= $entity_var_singular; ?>,
            'title' => "Editar <?= $singularName; ?>",
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{<?= $entity_identifier; ?>}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, <?= $entity_class_name; ?> $<?= $entity_var_singular; ?>)
    {
        if (!$this->isCsrfTokenValid('delete'.$<?= $entity_var_singular; ?>->get<?= ucfirst($entity_identifier); ?>(), $request->request->get('_token'))) {
            return $this->redirectToRoute('<?= $route_name; ?>_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($<?= $entity_var_singular; ?>);
        $em->flush();
        $this->addFlash('notice', '<?= $singularName; ?> Eliminada Correctamente');

        return $this->redirectToRoute('<?= $route_name; ?>_index');
    }

    /**
     * @Route("/remove/ajax", name="remove_ajax", methods={"POST"})
     */
    public function removeAjax(Request $request)
    {
        if($request->isXmlHttpRequest())
        {
            $id      = $request->request->get('id');
            $em      = $this->getDoctrine()->getManager();
            $entity  = $em->getRepository(<?= $entity_class_name; ?>::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException(
                    'Ningun <?= $singularName; ?> Encontrado'
                );
            }

            $em->remove($entity);
            $em->flush();

            $response = new JsonResponse();
            $response->setStatusCode(200);
            $response->setData(array(
                'response' => 'success',
                'id' => $id
            ));

            return $response;
        }

         return new Response('This is not ajax!', 400);
    }
}
