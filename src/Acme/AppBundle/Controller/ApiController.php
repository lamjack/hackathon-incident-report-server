<?php
/**
 * ApiController.php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    jack <linjue@wilead.com>
 * @copyright 2007-15/11/28 WIZ TECHNOLOGY
 * @link      http://wizmacau.com
 * @link      http://jacklam.it
 * @link      https://github.com/lamjack
 * @version
 */

namespace Acme\AppBundle\Controller;

use Acme\AppBundle\Jobs\EventPostJob;
use Kreait\Firebase\Query;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Network\Curl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiController
 * @package Acme\AppBundle\Controller
 */
class ApiController extends Controller
{
    /**
     * @ApiDoc(
     *     description="Media upload"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $files = array();
        $it = $request->files->getIterator();
        $webPath = realpath($this->container->get('kernel')->getRootDir() . '/../web');
        $saveFolder = 'uploads' . DIRECTORY_SEPARATOR . date('Y-m');

        while ($it->valid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $it->current();

            $filename = time() . rand(100000, 999999) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move(sprintf('%s/%s/', $webPath, $saveFolder), $filename);

            array_push($files, DIRECTORY_SEPARATOR . $saveFolder . DIRECTORY_SEPARATOR . $filename);

            $it->next();
        }

        return $this->createSuccessJsonResponse(array('files' => $files));
    }

    /**
     * @ApiDoc(
     *     description="Post event"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $firebase = $this->getFirebase();
        try {
            if (json_last_error()) {
                throw new \RuntimeException('json error');
            }
            $events = $firebase->getReference('data/events');
            $event = array_merge($data, array('timestamp' => time()));
            $events->push($event);

//            $curl = new Curl();
//            $curl->post('http://192.168.10.94:5000/incident', $data, array(
//                'json' => true
//            ));

            return $this->createSuccessJsonResponse();
        } catch (\RuntimeException $e) {
            return $this->createErrorJsonResponse(array('message' => $e->getMessage()));
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function submitAction(Request $request)
    {
        $firebase = $this->getFirebase();
        $webPath = realpath($this->container->get('kernel')->getRootDir() . '/../web');
        $saveFolder = 'uploads' . DIRECTORY_SEPARATOR . date('Y-m');
        try {
            if ($request->files->has('image')) {
                /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $request->files->get('image');
                $filename = time() . rand(100000, 999999) . '.' . strtolower($file->getClientOriginalExtension());
                $file->move(sprintf('%s/%s/', $webPath, $saveFolder), $filename);
                $image = DIRECTORY_SEPARATOR . $saveFolder . DIRECTORY_SEPARATOR . $filename;
                $data = array(
                    'media_url' => $image
                );
                foreach ($request->request->getIterator() as $k => $v) {
                    $data[$k] = $v;
                }
                $events = $firebase->getReference('data/events');
                $event = array_merge($data, array('timestamp' => time()));
                $events->push($event);
                return $this->createSuccessJsonResponse();
            }
            throw new \RuntimeException('images is not exist');
        } catch (\RuntimeException $e) {
            return $this->createErrorJsonResponse(array('message' => $e->getMessage()));
        }
    }

    /**
     * @ApiDoc(
     *     description="Get event data"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $firebase = $this->getFirebase();
        try {
            $events = $firebase->getReference('data/events');
            $query = new Query();
            $query
                ->orderByChildKey('timestamp');
            return $this->createSuccessJsonResponse(array('events' => $events->query($query)));
        } catch (\RuntimeException $e) {
            return $this->createErrorJsonResponse(array('message' => $e->getMessage()));
        }
    }

    /**
     * @return \Kreait\Firebase\Firebase
     */
    protected function getFirebase()
    {
        return $this->get('kreait_firebase.connection.main');
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    protected function createSuccessJsonResponse($data = array())
    {
        return $this->createJsonResponse(true, $data);
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    protected function createErrorJsonResponse($data = array())
    {
        return $this->createJsonResponse(false, $data);
    }

    /**
     * @param $status
     * @param array $data
     *
     * @return JsonResponse
     */
    protected function createJsonResponse($status, $data = array())
    {
        $response = new JsonResponse(array(
            'status' => $status,
            'data' => $data
        ));

        $response->headers->set('Access-Control-Allow-Headers', 'origin, content-type, accept');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, PATCH, OPTIONS');

        return $response;
    }
}