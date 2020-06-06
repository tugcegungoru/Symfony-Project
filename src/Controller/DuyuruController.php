<?php

namespace App\Controller;

use App\Entity\Duyuru;
use App\Form\Duyuru1Type;
use App\Repository\DuyuruRepository;
use phpDocumentor\Reflection\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/duyuru")
 */
class DuyuruController extends AbstractController
{
    /**
     * @Route("/", name="user_duyuru_index", methods={"GET"})
     */
    public function index(DuyuruRepository $duyuruRepository): Response
    {
        $user = $this->getUser();
        return $this->render('duyuru/index.html.twig', [
            'duyurus' => $duyuruRepository->findBy(['userid' => $user->getId()]),
        ]);
    }

    /**
     * @Route("/new", name="user_duyuru_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $duyuru = new Duyuru();
        $form = $this->createForm(Duyuru1Type::class, $duyuru);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            /** @var file $file */
            //*****************file upload****************
            $file = $form['image']->getData(); //formdan gelen img get data olarak alıyor
            if ($file) { //eğer bu dosya varsa bu alana bir dosya select edilsiyse buraya gir
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension(); //bir tane rastgele dosya adı oluştur.4fffkjdf556f4fkfdldkf.jpeg gibi benzersiz bir isim olusturur
                //dosya adı bu olacak ve (.) uzantısını al birleştir
                try {
                    $file->move(  //dosyayı al move et
                        $this->getParameter('images_directory'), //image/direct kaydet
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $duyuru->setImage($fileName);
            }
            //**************file upload***************
            $user= $this->getUser();
            $duyuru->setUserid($user->getId());

            $duyuru->setStatus("New");

            $entityManager->persist($duyuru);
            $entityManager->flush();

            return $this->redirectToRoute('user_duyuru_index');
        }

        return $this->render('duyuru/new.html.twig', ['duyuru' => $duyuru,
            'duyuru'=> $duyuru,
            'form' => $form->createView(),]);
    }
    /**
     * @Route("/{id}", name="user_duyuru_show", methods={"GET"})
     */
    public function show(Duyuru $duyuru): Response
    {
        return $this->render('duyuru/show.html.twig', [
            'duyuru' => $duyuru,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_duyuru_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Duyuru $duyuru): Response
    {
        $form = $this->createForm(Duyuru1Type::class, $duyuru);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var @var file $file */
            //*****************file upload****************
            $file = $form['image']->getData(); //formdan gelen img get data olarak alıyor
            if ($file) { //eğer bu dosya varsa bu alana bir dosya select edilsiyse buraya gir
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension(); //bir tane rastgele dosya adı oluştur.4fffkjdf556f4fkfdldkf.jpeg gibi benzersiz bir isim olusturur
                //dosya adı bu olacak ve (.) uzantısını al birleştir
                try {
                    $file->move(  //dosyayı al move et
                        $this->getParameter('images_directory'), //image/direct kaydet
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $duyuru->setImage($fileName);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_duyuru_index');
        }

        return $this->render('duyuru/edit.html.twig', [
            'duyuru' => $duyuru,
            'form' => $form->createView(),
        ]);
    }


    private function generateUniqueFileName(){ //benzersiz jpeg resmi adı oluşturur
        return md5(uniqid());
    }


    /**
     * @Route("/{id}", name="user_duyuru_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Duyuru $duyuru): Response
    {
        if ($this->isCsrfTokenValid('delete'.$duyuru->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($duyuru);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_duyuru_index');
    }
}
