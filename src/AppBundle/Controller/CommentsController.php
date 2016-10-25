<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class CommentsController extends Controller
{

    public function indexAction(Request $request)
    {
        try {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Bookmark');
            $query = $repository->createQueryBuilder('b');
            $result = $query
                ->select('b.uid, b.createdAt, b.url')
                ->orderBy('b.createdAt', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getArrayResult();
        } catch (\Exception $e) {
            $response = new Response(json_encode(array()), Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }

        return new Response(json_encode($result));
    }

    public function addAction(Request $request)
    {
        $bookmarkUid = trim($request->get('bookmark_uid'));
        $bookmark = $this->getDoctrine()->getRepository('AppBundle:Bookmark')
            ->findOneBy(array('uid'=>$bookmarkUid));

        try {
            $entity = new \AppBundle\Entity\Comment();
            $entity->setBookmark($bookmark);
            $entity->setIp($request->getClientIp());
            $entity->setUid(Uuid::uuid4()->toString());
            $entity->setCreatedAt(new \DateTime());
            $entity->setText($request->get('text'));

            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();

        } catch (\Exception $exception) {
            $message = $exception->getMessage();

            $response = new Response();
            $response->setContent(json_encode(array('Success' => false, 'Message' => $message)));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
        return new Response(json_encode(array('Success' => true, 'uid' => $entity->getUid())));
    }

    public function deleteAction(Request $request)
    {
        try {

            if ($comment = $this->getComment($request)) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($comment);
                $em->flush();
            }
            else {
                return new Response(json_encode(array('Success' => false)));
            }

        } catch (\Exception $exception) {
            $message = $exception->getMessage();

            $response = new Response();
            $response->setContent(json_encode(array('Success' => false, 'Message' => $message)));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
        return new Response(json_encode(array('Success' => true)));
    }

    public function updateAction(Request $request)
    {
        try {

            if ($comment = $this->getComment($request)) {
                $em = $this->getDoctrine()->getManager();
                $comment->setText($request->get('text'));
                $em->flush();
            }
            else {
                return new Response(json_encode(array('Success' => false)));
            }

        } catch (\Exception $exception) {
            $message = $exception->getMessage();

            $response = new Response();
            $response->setContent(json_encode(array('Success' => false, 'Message' => $message)));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
        return new Response(json_encode(array('Success' => true)));
    }

    private function getComment(Request $request)
    {
        $uid = trim($request->get('uid'));

        $repository = $this->getDoctrine()->getRepository('AppBundle:Comment');

        $query = $repository->createQueryBuilder('c');
        $query->andWhere('c.uid = :uid')->setParameter('uid', $uid);
        $query->andWhere('c.ip = :ip')->setParameter('ip', $request->getClientIp());

        $oneHourAgo = new \DateTime;
        $oneHourAgo = $oneHourAgo->sub(new \DateInterval('PT1H'));
        $query->andWhere('c.createdAt > :oneHourAgo')
            ->setParameter('oneHourAgo', $oneHourAgo);

        return $query->getQuery()->getOneOrNullResult();
    }
}