<?php

/**
 * Game Controller for Instant Lottery module.
 */

namespace Modules\GameInstantLottery\Controllers;

use Johncms\Http\Request;
use Johncms\Http\Response\RedirectResponse;
use Johncms\Http\Session;
use Johncms\Registry;
use Modules\GameInstantLottery\Models\Player;
use Modules\GameInstantLottery\Services\GameService;

class GameController extends \Johncms\Controller\BaseController
{
    /**
     * @var Registry
     */
    private $systemRegistry;

    /**
     * @var GameService
     */
    private $gameService;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor.
     *
     * @param Registry    $systemRegistry System Registry
     * @param GameService $gameService
     * @param Request     $request       Request
     * @param Session     $session       Session
     */
    public function __construct(
        Registry $systemRegistry,
        GameService $gameService,
        Request $request,
        Session $session
    ) {
        parent::__construct($systemRegistry);
        $this->loadConfig();
        $this->session = $session;

        if (! $this->session->has('gameSession')) {
            $this->session->set('gameSession', []);
        }
        if (! $this->session->has('cellTypes')) {
            $this->session->set('cellTypes', $this->config['cell_types']);
        }
        $this->systemRegistry = $systemRegistry;
        $this->gameService = $gameService;
        $this->request = $request;
    }

    /**
     * Index Page
     *
     * @return string
     */
    public function index(): string
    {
        return '';
    }

    /**
     * Start Game
     *
     * @return RedirectResponse
     */
    public function start(): RedirectResponse
    {
        return new RedirectResponse('/');
    }

    /**
     * Move Action
     *
     * @return RedirectResponse
     */
    public function move(): RedirectResponse
    {
        return new RedirectResponse('/');
    }

    /**
     * Cashout Action
     *
     * @return RedirectResponse
     */
    public function cashout(): RedirectResponse
    {
        return new RedirectResponse('/');
    }
}