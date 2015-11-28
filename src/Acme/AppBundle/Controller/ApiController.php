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

use Kreait\Firebase\Query;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @ApiDoc(
     *     description="Media upload"
     * )
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
        return new JsonResponse(array('status' => true, 'data' => $files));
    }

    /**
     * @ApiDoc(
     *     description="Post event"
     * )
     */
    public function postAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $firebase = $this->getFirebase();
        try {
            if (json_last_error()) {
                throw new \RuntimeException(json_last_error_msg());
            }
            $events = $firebase->getReference('data/events');
            $events->push(array_merge($data, array('timestamp' => time())));
            return new JsonResponse(array('status' => true));

        } catch (\RuntimeException $e) {
            return $this->createErrorJsonResponse(array('message' => $e->getMessage()));
        }
    }

    /**
     * @ApiDoc(
     *     description="Get event data"
     * )
     */
    public function getAction(Request $request)
    {
        $firebase = $this->getFirebase();
        try {
            $events = $firebase->getReference('data/events');
            $query = new Query();
            $query
                ->orderByChildKey('timestamp');
            return $this->createSuccessJsonResponse(array('events' => $events->getData()));
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
    protected function createErrorJsonResponse($data = array())
    {
        return new JsonResponse(array('status' => false, 'data' => $data));
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    protected function createSuccessJsonResponse($data = array())
    {
        return new JsonResponse(array('status' => true, 'data' => $data));
    }
}