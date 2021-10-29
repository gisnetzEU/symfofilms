<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Actor;
use App\Form\ActorFormType;
use App\Form\ActorDeleteFormType;

class ActorController extends AbstractController
{
   /* #[Route('/actor', name: 'actor')]
    public function index(): Response
    {
        return $this->render('actor/index.html.twig', [
            'controller_name' => 'ActorController',
        ]);
    }
    */

  /**
     * @Route("/actores", name="actor_list")
     */

    public function index(): Response
    {
        //recuperamos los actores haciendo uso del repositorio ActorRepository,
        //haciendo uso del método findAll()
        $actores = $this->getDoctrine()
            ->getRepository(Actor::class)
            ->findall();

        //cargamos la vista con el listado de actores y le pasamos los actores recuperados
        return $this->render('actor/list.html.twig', [
            'actores' => $actores,
        ]);
    }

    /**
     * @Route("/actor/store", name="actor_store")
     */
    public function store(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $actuante = new Actor();
        $actuante->setNom('Julia');
        $actuante
            ->setDatadenaixement(new \DateTime())
            ->setNacionalitat('estadounidense')
            ->setBiografia('Actriz estadounidense que se hizo famosa con la película Pretty Woman');

        $entityManager->persist($actuante);
        $entityManager->flush();

        return new Response('Actor guardada con id ' . $actuante->getId());
    }

    /**
     * @Route("/actor/{id<\d+>}", name="actor_show")
     */

    public function show(Actor $actuante): Response
    {
        //retorna la respuesta (normalmente será una vista)
        return $this->render('actor/show.html.twig', ['actor' => $actuante]);
    }

    /**
     * @Route("/actor/search/{campo}/{valor}", name="actor_search")
     */

    public function search($campo, $valor): Response
    {
        //recuperar los actores
        $criterio = [$campo => $valor];
        $actores = $this->getDoctrine()
            ->getRepository(Actor::class)
            ->findBy($criterio);

        //retorna la respuesta (normalmente será una vista)
        return new Response('Lista de actores.<br>' . implode('<br>', $actores));
    }

    /**
     * @Route("/actor/update/{id}")
     */

    public function update($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $actuante = $entityManager->getRepository(Actor::class)->find($id);

        //sino existe el actuante lanzamos una excepción
        if (!$actuante) {
            throw $this->createNotFoundException("No se encontró el actor $id");
        }

        $actuante->setNom('Tom Hanks'); //cambiamos el título
        $entityManager->flush(); //aplicamos los cambios

        //rederigimos el método show
        return $this->redirectToRoute('actor_show', ['id' => $id]);
    }

    /**
     * @Route("/actor/destroy/{id}")
     */

    public function destroy($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $actuante = $entityManager->getRepository(Actor::class)->find($id);

        //sino existe el actor lanzamos una excepción

        if (!$actuante) {
            throw $this->createNotFoundException("No se encontró el actor $id");
        }

        $entityManager->remove($actuante); //indica que hay que borrar el actor
        $entityManager->flush(); //aplicamos los cambios

        //rederigimos el método show
        return new Response(
            "El actor <b>$actuante</b> fue eliminado correctamente."
        );
    }

    /**
     * @Route("/actor/create", name="actor_create")
     */
    public function create(Request $request): Response
    {
        $actuante = new Actor();

        $formulario = $this->createFormBuilder($actuante)
            ->add('nom', TextType::class)
            ->add('datadenaixement', DateType::class, [
                'empty_data' => 0,
                'html5' => true,
            ])
            ->add('nacionalitat', TextType::class)
            ->add('biografia', TextareaType::class)            
            ->add('Guardar', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success my-3'],
            ])
            ->getForm();

        $formulario->handleRequest($request);

        //$formulario = $this->createForm(ActorFormType::class, $actuante);

        //si el formulario ha sido enviado y es valido

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            //almacenar los datos del actuante en la BDD
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($actuante);
            $entityManager->flush();

            //flashear el mensaje
            $this->addFlash(
                'success',
                'Actor guardada con id ' . $actuante->getId()
            );

            return $this->redirectToRoute('actor_show', [
                'id' => $actuante->getID(),
            ]);
        }

        //retornar la vista
        return $this->render('actor/create.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }

    /**
     * @Route("/actor/edit/{id}", name="actor_edit")
     */

    public function edit(Actor $actuante, Request $request): Response
    {
        //crea el formulario
        $formulario = $this->createForm(ActorFormType::class, $actuante);
        $formulario->handleRequest($request);

        //si el formulario fue enviado y es válido
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            //guarda los cambios en la BDD
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush(); //ejecuta las consultas

            //flashear el mensaje
            $this->addFlash('success', 'Película actualizada correctamente');

            //redirige a "ver detalles del actuante"
            return $this->redirectToRoute('actor_show', [
                'id' => $actuante->getId(),
            ]);
        }

        //carga la vista con el formulario
        return $this->render('actor/edit.html.twig', [
            'formulario' => $formulario->createView(),
            'actor' => $actuante,
        ]);
    }

    /**
     * @Route("/actor/delete/{id}", name="actor_delete")
     */

    public function delete(Actor $actuante, Request $request): Response
    {
        //crea el formulario
        $formulario = $this->createForm(ActorDeleteFormType::class, $actuante);
        $formulario->handleRequest($request);

        //si el formulario fue enviado y es válido
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            //guarda los cambios en la BDD
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actuante);
            $entityManager->flush(); //ejecuta las consultas

            //flashear el mensaje
            $this->addFlash('success', 'Película eliminada correctamente');

            //redirige a la lista de actores
            return $this->redirectToRoute('actor_list');
        }

        //muestra el formulario de confirmación de borrado
        return $this->render('actor/delete.html.twig', [
            'formulario' => $formulario->createView(),
            'actor' => $actuante,
        ]);
    }
}


