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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

    }

    /**
     * @ApiDoc(
     *     description="Post event"
     * )
     */
    public function postAction(Request $request)
    {

    }

    /**
     * @ApiDoc(
     *     description="Get event data"
     * )
     */
    public function getAction(Request $request)
    {

    }
}