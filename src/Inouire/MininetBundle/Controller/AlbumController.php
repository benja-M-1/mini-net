<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;

class AlbumController extends Controller
{
    
    /*
     * Redirects to the album of the current year
     */
    public function viewCurrentAction(){
        return $this->redirect($this->generateUrl('album',array(
            'year' => date('Y')
        )));
    }
    
    /*
     * Handles the album of a given year
     */
    public function viewAction($year){
        
        //get entity manager and Post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //get all the posts of the given year
        $post_list = $post_repo->getYearlyPosts($year);
        
        //render the automatic album
        return $this->render('InouireMininetBundle:Default:album.html.twig',array(
            'year' => $year,
            'post_list' => $post_list,
        ));
    }
    
    
}
