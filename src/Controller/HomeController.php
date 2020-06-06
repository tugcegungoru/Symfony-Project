<?php

namespace App\Controller;

use APP\Entity\Setting;
use App\Entity\Admin\Messages;
use App\Entity\Duyuru;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\CommentRepository;
use App\Repository\DuyuruRepository;
use App\Repository\ImageRepository;
use App\Repository\SettingRepository;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository, DuyuruRepository $duyuruRepository)
    {
        $setting=$settingRepository->findAll();
        $slider=$duyuruRepository->findBy([],['title'=>'ASC'],5);
        $announcements=$duyuruRepository->findBy([],['title'=>'DESC'],5);
        $news=$duyuruRepository->findBy([],['title'=>'DESC'],3);

        #dump($slider);
        #die();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
            'announcements'=>$announcements,
            'news'=>$news,
        ]);
    }

    /**
     * @Route("/duyuru/{id}", name="duyuru_show", methods={"GET"})
     */
    public function show(Duyuru $duyuru,$id, ImageRepository $imageRepository,CommentRepository $commentRepository): Response
    {
        $images=$imageRepository->findBy(['duyuru'=>$id]);
        $comments=$commentRepository->findBy(['duyuruid'=>$id, 'status'=>'True']);

        return $this->render('home/duyurushow.html.twig', [
            'duyuru' => $duyuru,
            'images' => $images,
            'comments' => $comments,
        ]);
    }


    /**
     * @Route("/about", name="home_about", methods={"GET"})
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting' => $setting,
        ]);
    }


    /**
     * @Route("/contact", name="home_contact", methods={"GET","POST"})
     */
    public function contact(SettingRepository $settingRepository,Request $request): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token'); //Submit token ile key tooken oluşturuyoruz

        $setting=$settingRepository->findAll(); //Get setting data

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-message',$submittedToken)){  //form message ile eşitse içeri girecek
                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success', 'Your message sent successfully');

                //************SEND MAİL************
                $email=(new Email())
                    ->from($setting[0]->getSmtpemail()) //veritabanından
                    ->to($form['email']->getData())  //mailden
                    ->subject('Announcement')
                    ->html("Dear ".$form['name']->getData()."<br>
                                   <p> We will evaluate your requests and contact you as soon as possible</p>
                                   Thank your for messages <br>
                                   =========================================================
                                   <br>".$setting[0]->getCompany()." <br>
                                   Address :".$setting[0]->getAddress()." <br>
                                   Phone   : ".$setting[0]->getPhone()."<br>"
                    );
                $transport= new GmailTransport($setting[0]->getSmtpemail(),$setting[0]->getSmtppassword());
                $mailer= new Mailer($transport);
                $mailer->send($email);

                //**************************SEND MAİL**************
                return $this->redirectToRoute('home_contact');
            }
        }

        $setting=$settingRepository->findAll();
        return $this->render('home/contact.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),

        ]);
    }

}

