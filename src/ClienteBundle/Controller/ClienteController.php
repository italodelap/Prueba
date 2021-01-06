<?php

namespace ClienteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ClienteBundle\Entity\Cliente;
use ClienteBundle\Form\ClienteType;

class ClienteController extends Controller
{
    public function homeAction(Request $request)
    {
        $searchQuery = $request->get('query');
        $em = $this->getDoctrine()->getManager();

        if (!empty($searchQuery))
            $listaClientes = $em->getRepository('ClienteBundle:Cliente')->buscarClientes($searchQuery);
        else
            $listaClientes = $em->getRepository('ClienteBundle:Cliente')->listarClientes();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $listaClientes,
            $request->query->getInt('page', 1),
            5
        );

        $formElim_ajax = $this->crearFormularioPersonalizado(':USER_ID', 'DELETE', 'cliente_cliente_borrar');
        
        return $this->render('ClienteBundle:Cliente:home.html.twig', array(
            'pagination' => $pagination,
            'formElim_ajax' => $formElim_ajax->createView()
        ));
    }

    private function crearFormularioPersonalizado($id, $metodo, $ruta)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($ruta, array(
                'id' => $id
            )))
            ->setMethod($metodo)
            ->getForm();
    }

    public function agregarAction()
    {
        $cliente = new Cliente;
        $form = $this->crearFormularioCreacion($cliente);

        return $this->render('ClienteBundle:Cliente:agregar.html.twig', array(
            'form' => $form->createView()
        ));
    }

    private function crearFormularioCreacion(Cliente $entity)
    {
        $form = $this->createForm(new ClienteType(), $entity, array(
            'action' => $this->generateUrl('cliente_cliente_crear'),
            'method' => 'POST'
        ));

        return $form;
    }

    public function crearAction(Request $request)
    {
        $cliente = new Cliente();
        $form = $this->crearFormularioCreacion($cliente);
        $form->handleRequest($request);

        if ($form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($cliente);
            $em->flush();

            $this->addFlash('success', 'El cliente fue creado correctamente');

            return $this->redirectToRoute('cliente_cliente_home');
        }

        return $this->render('ClienteBundle:Cliente:agregar.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function verAction($id)
    {
        $cliente = $this->getDoctrine()->getRepository('ClienteBundle:Cliente')->findOneById($id);
        if (!$cliente)
            throw $this->createNotFoundException('Cliente no encontrado');
        
        $formElim = $this->crearFormularioPersonalizado($cliente->getId(), 'DELETE', 'cliente_cliente_borrar');
        
        return $this->render('ClienteBundle:Cliente:ver.html.twig', array(
            'cliente' => $cliente,
            'formElim' => $formElim->createView()
        ));
    }

    public function borrarAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $cliente = $em->getRepository('ClienteBundle:Cliente')->findOneById($id);
        if (!$cliente)
            throw $this->createNotFoundException('Cliente no encontrado');
        
        $totalClientes = $em->getRepository('ClienteBundle:Cliente')->findAll();
        $totalRegistros = count($totalClientes);
        
        $form = $this->crearFormularioPersonalizado($cliente->getId(), 'DELETE', 'cliente_cliente_borrar');
        $form->handleRequest($request);

        $removed = 0;

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($request->isXMLHttpRequest())
            {
                $em->remove($cliente);
                $em->flush();
                $removed = 1;

                return new Response(
                    json_encode(
                        array(
                            'removed' => $removed,
                            'mensaje' => 'El cliente fue eliminado correctamente',
                            'totalRegistros' => $totalRegistros
                    )),
                    200,
                    array('Content-Type' => 'application/json')
                );
            }

            $em->remove($cliente);
            $em->flush();

            $this->addFlash('success', 'El cliente fue eliminado correctamente');

            return $this->redirectToRoute('cliente_cliente_home');
        }
    }

    public function editarAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $cliente = $em->getRepository('ClienteBundle:Cliente')->findOneById($id);
        
        if (!$cliente)
            throw $this->createNotFoundException('Cliente no encontrado');

        $form = $this->crearFormularioEdicion($cliente);

        return $this->render('ClienteBundle:Cliente:editar.html.twig', array(
            'cliente' => $cliente,
            'form' => $form->createView()
        ));
    }

    private function crearFormularioEdicion(Cliente $entity)
    {
        $form = $this->createForm(new ClienteType(), $entity, array(
            'action' => $this->generateUrl('cliente_cliente_actualizar', array(
                'id' => $entity->getId()
            )),
            'method' => 'PUT'
        ));

        return $form;
    }

    public function actualizarAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $cliente = $em->getRepository('ClienteBundle:Cliente')->findOneById($id);
        
        if (!$cliente)
            throw $this->createNotFoundException('Cliente no encontrado');
        
        $form = $this->crearFormularioEdicion($cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'El cliente fue editado correctamente');

            return $this->redirectToRoute('cliente_cliente_editar', array(
                'id' => $cliente->getId()
            ));
        }

        return $this->render('ClienteBundle:Cliente:editar.html.twig', array(
            'cliente' => $cliente,
            'form' => $form->createView()
        ));
    }
}