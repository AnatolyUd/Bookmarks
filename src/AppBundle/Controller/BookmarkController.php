<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class BookmarkController extends Controller
{

    public function indexAction(Request $request)
    {
        $url = $request->get('url');
        if (!empty($url)) {
            return $this->findOne($url);
        }

        try {
            $repository = $this->getDoctrine()->getRepository("AppBundle:Bookmark");
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

    private function findOne($url)
    {
        try {
            $uid = Uuid::uuid3(Uuid::NAMESPACE_URL, $url)->toString();

            $repository = $this->getDoctrine()->getRepository("AppBundle:Bookmark");
            $query = $repository->createQueryBuilder('b');
            $result = $query
                ->select('b.id, b.uid, b.createdAt, b.url')
                ->andWhere('b.uid = :uid')->setParameter('uid', $uid)
                ->getQuery()
                ->getArrayResult();



            if ($result){
                $bookmarkId = $result[0]['id'];
                unset($result[0]['id']);

                $repository = $this->getDoctrine()->getRepository("AppBundle:Comment");
                $query = $repository->createQueryBuilder('c');
                $comments = $query
                    ->select('c.uid, c.createdAt, c.text')
                    ->andWhere('c.bookmark = :bookmarkId')->setParameter('bookmarkId', $bookmarkId)
                    ->getQuery()
                    ->getArrayResult();
                $result[0]['comments'] = $comments;
            }

        } catch (\Exception $e) {
            $response = new Response(json_encode(array()), Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }

        return new Response(json_encode($result));
    }


    public function addAction(Request $request)
    {
        $url = trim($request->get('url'));
        $uid = Uuid::uuid3(Uuid::NAMESPACE_URL, $url)->toString();
        $repository = $this->getDoctrine()->getRepository("AppBundle:Bookmark");
        $entity = $repository->findOneByUid($uid);
        if (!$entity) {
            try {
                $entity = new \AppBundle\Entity\Bookmark();
                $entity->setUrl($url);
                $entity->setUid($uid);
                $entity->setCreatedAt(new \DateTime());

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
        }
        return new Response(json_encode(array('Success' => true, 'uid' => $entity->getUid())));
    }

}
