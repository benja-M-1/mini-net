<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;

class DefaultController extends Controller
{
    /*
     * Handles redirection of the root / of the website
     */
    public function rootAction(){
        return $this->redirect($this->generateUrl('home'));
    }
    
    
    /**
     * Handles the home page 
     */
    public function homeAction(){
        
        //get entity manager and post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //retrieve last 6 published posts from database
        $post_list = $post_repo->findBy(
            array('published' => true),
            array('date' => 'desc'),
            8,
            0
        );
        
        $post_secondary_list = $post_repo->findBy(
            array('published' => true),
            array('date' => 'desc'),
            6,
            8
        );

        if(count($post_list)==0){
            return $this->render('InouireMininetBundle:Empty:emptyHome.html.twig');
    }else{
            return $this->render('InouireMininetBundle:Main:home.html.twig',array(
                'post_list'=> $post_list,
                'post_secondary_list' => $post_secondary_list
            ));
    }
    }
    
    /*
     * Redirect archives root URL to the current year-month archives
     */
    public function archivesAction(){
        
        return $this->redirect($this->generateUrl('posts',array(
            'year' => date('Y'),
            'month' => date('m')
        )));
        
    }
    
    /*
     * Handles the archives of a given year and month
     */
    public function postsAction($year,$month){

        //check validity of year and month given
        if( $year > 8000 || $year < 1 || $month < 1 || $month > 12){
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_title'=> 'Date invalide',
                'error_message' => 'Impossible de récupérer les posts du mois '.$month.' / année '.$year,
                'follow_link' => $this->generateUrl('archives'),
                'follow_link_text' => 'Aller aux archives du mois en cours',
            ));
        }
        
        //get entity manager and post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //retrieve posts of the requested month
        $monthly_posts = $post_repo->getMonthlyPosts($year,$month);
       
        //build date of current month
        $requested_date = new \Datetime($year.'-'.$month.'-01');
        
        //build months of year
        $months_of_year = array();
        for( $m = 1 ; $m <=12 ; $m++){
            $months_of_year[] = new \Datetime('2000-'.$m.'-01');
        }
        
        //check that there are some posts for this month
        if( count($monthly_posts) > 0 ){
            return $this->render('InouireMininetBundle:Main:archives.html.twig',array(
                'post_list'=> $monthly_posts,
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year
            ));
        }else{ //if no post available, display the specific page
            return $this->render('InouireMininetBundle:Empty:noArchives.html.twig',array(
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year
            ));
        }
        
    }
    
    
}
