<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;


class ImageController extends Controller
{
    
    public function addImageAction(){
        
        $image = new Image();
        
        $form = $this->createFormBuilder($image)
            ->add('file','file')
            ->add('post_id','hidden')
            ->getForm();
            
        if ($this->getRequest()->getMethod() === 'POST') {
            
            $form->bindRequest($this->getRequest());
            
            if ($form->isValid()) {
                
                //save file to disk
                $newFilename = rand(1000000, 999999999).rand(1000000, 999999999);
                $image->file->move($image->getUploadDir(), $newFilename);
  
                //catch exceptions ?              

                //try to guess the extension
                $file=new File($image->getUploadDir().'/'.$newFilename,true);
                
                $extension = $file->guessExtension();
                if (!$extension) {
                    // extension cannot be guessed
                    $extension = 'bin';
                }
                $file->move($image->getUploadDir(), $newFilename.'.'.$extension);
                
                //cleaning file property (not needed any more at this step)
                $image->file = null;
                
                //starting doctrine operations
                $em = $this->getDoctrine()->getEntityManager();
                $image->setPath($newFilename.'.'.$extension);
                $image->setPost($em->getRepository('InouireMininetBundle:Post')->find($image->post_id));
                $em->persist($image);
                $em->flush();

                return $this->redirect($this->generateUrl('edit_post',array('post_id' => $image->post_id )));
                
            }   
        }else{
            return $this->render('InouireMininetBundle:Default:imageForm.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }
    
    public function getImageAction($image_id){

        
    }
    
    /*
     * Controller for delete action on an image
     */ 
    public function deleteImageAction($image_id){
        
        //get entity manager
        $em = $this->getDoctrine()->getEntityManager();
        
        //get image
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //check that this image exists and that it belongs to this user
        if($image==null ){
            $response_status = 'error';
            $response_message = 'image '.$image_id.' does not exist';
        }else if( $image->getPost()->getAuthor() != $user){
            $response_status = 'error';
            $response_message = 'image '.$image_id.' does not belong to you';
        } else {
            //delete image
            $em->remove($image);
            $response_status = 'ok';
            $response_message = 'image '.$image_id.' has been deleted';
  
            //persit changes in the database
            $em->flush();
        }
        
        //render json response
        return $this->render('InouireMininetBundle:Post:ajaxResponse.json.twig',array(
            'status'=> $response_status,
            'message' => $response_message
        ));
    }
    
    
}