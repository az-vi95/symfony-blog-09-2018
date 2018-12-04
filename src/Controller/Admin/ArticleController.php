<?php


namespace App\Controller\Admin;


use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller\Admin
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        /**
         *
         * faire la page qui liste les articles dans un tableau html
         * avec un nom de categorie
         * nom de lauteur
         * et date au format francais
         * tous les champs sauf contenu
         */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Article::class);
        $articles = $repository->findBy([],['publicationDate' => 'desc']);


        return $this->render(
            'admin/article/index.html.twig',
            [
                'articles' => $articles
            ]
        );
    }

    /**
     * ajouter la méthode edit() qui fait le rendu du formulaire et son traitement
     * mettre un lien ajouter dans la page de liste
     *
     * Validation : tous les champs obligatoire
     *
     * En création :
     * - setter l'auteur avec l'utilisateur connecté
     *      ($this->getUser() depuis le controleur )
     * - mettre la date de publication à maintenant
     *
     * Adapter la route et le contenu de la méthode
     * pour que la page fonctionne en modification
     * et ajouter le bouton modifier dans page de liste la liste
     *
     * Enregistrer l'article en bdd si le formulaire est bien rempli
     * puis rediriger vers la liste avec un message de confirmation
     */

    /**
     * @Route("/edition/{id}", defaults={"id": null}, requirements={"id": "\d+"})
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (is_null($id)) { // création
            $article = new Article();
        } else { // modification
            $article = $em->find(Article::$id);

            // 404 si l'id reçu dans l'url n'est pas en bdd
            if (is_null($article)) {
                throw new NotFoundHttpException();
            }
        }


        // création du formulaire lié à l'article
        $form = $this->createForm(ArticleType::class, $article);
        // le formulaire analyse la requête HTTP
        // et traite le formulaire s'il a été soumis
        $form->handleRequest($request);

        // si le formulaire a été envoyé
        if ($form->isSubmitted()) {
            dump($article);

            // si les validations à partir des annotations
            // dans l'entité Artcle sont ok
            if ($form->isValid()) {
                // enregistrement de l'article en bdd
                $em->persist($article);
                $em->flush();

                // message de confirmation
                $this->addFlash('success', 'L\'article est enregistrée');
                // redirection vers la liste
                return $this->redirectToRoute('app_admin_article_index');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }





        return $this->render(
            'admin/article/edit.html.twig',
            [
                // passage du formulaire au template
            'form' => $form->createView()
            ]

        );
    }



}
