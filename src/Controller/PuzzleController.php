<?php

declare(strict_types=1);

namespace App\Controller;

use App\Puzzle\PuzzleMode;
use App\Puzzle\PuzzleSolver;
use App\Puzzle\PuzzleState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PuzzleController extends AbstractController
{
    private const string SESSION_POSITIONS_KEY = 'puzzle.positions';
    private const string SESSION_MODE_KEY = 'puzzle.mode';

    /**
     * @var array<int, string>
     */
    private const array IMAGE_MAP = [
        0 => 'pos0.PNG',
        1 => 'pos1.PNG',
        2 => 'pos2.PNG',
        3 => 'pos3.PNG',
    ];

    public function __construct(
        private readonly PuzzleSolver $solver,
    ) {
    }

    #[Route('/', name: 'app_puzzle_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->renderPuzzle($request);
    }

    #[Route('/move/{index}', name: 'app_puzzle_move', methods: ['POST'], requirements: ['index' => '\d+'])]
    public function move(int $index, Request $request): Response
    {
        if ($index < 0 || $index >= PuzzleState::DIAL_COUNT) {
            throw $this->createNotFoundException();
        }

        $state = $this->readState($request)->rotate($index, $this->readMode($request));
        $request->getSession()->set(self::SESSION_POSITIONS_KEY, $state->positions());

        if ($request->isXmlHttpRequest()) {
            return $this->renderPuzzle($request);
        }

        return $this->redirectToRoute('app_puzzle_index');
    }

    #[Route('/mode', name: 'app_puzzle_mode', methods: ['POST'])]
    public function mode(Request $request): Response
    {
        $submittedMode = PuzzleMode::tryFrom((string) $request->request->get('mode'));
        $mode = $submittedMode ?? PuzzleMode::Joint;

        $request->getSession()->set(self::SESSION_MODE_KEY, $mode->value);

        if ($request->isXmlHttpRequest()) {
            return $this->renderPuzzle($request);
        }

        return $this->redirectToRoute('app_puzzle_index');
    }

    #[Route('/reset', name: 'app_puzzle_reset', methods: ['POST'])]
    public function reset(Request $request): Response
    {
        $request->getSession()->set(self::SESSION_POSITIONS_KEY, PuzzleState::initial()->positions());

        if ($request->isXmlHttpRequest()) {
            return $this->renderPuzzle($request);
        }

        return $this->redirectToRoute('app_puzzle_index');
    }

    private function renderPuzzle(Request $request): Response
    {
        $template = $request->isXmlHttpRequest() ? 'puzzle/_app.html.twig' : 'puzzle/index.html.twig';

        return $this->render($template, $this->buildViewModel($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewModel(Request $request): array
    {
        $session = $request->getSession();
        $state = $this->readState($request);
        $mode = $this->readMode($request);
        $analysis = $this->solver->analyze($state, $mode);

        $session->set(self::SESSION_POSITIONS_KEY, $state->positions());
        $session->set(self::SESSION_MODE_KEY, $mode->value);

        $dials = [];
        foreach ($state->positions() as $index => $position) {
            $dials[] = [
                'index' => $index,
                'label' => $index + 1,
                'position' => $position,
                'image' => self::IMAGE_MAP[$position],
            ];
        }

        return [
            'dials' => $dials,
            'positions' => $state->positions(),
            'mode' => $mode,
            'modes' => PuzzleMode::cases(),
            'analysis' => $analysis,
            'isSolved' => $state->isSolved(),
            'solutionPath' => array_map(
                static fn (int $index): int => $index + 1,
                $analysis->firstSolution,
            ),
        ];
    }

    private function readState(Request $request): PuzzleState
    {
        $positions = $request->getSession()->get(self::SESSION_POSITIONS_KEY, PuzzleState::initial()->positions());

        if (!is_array($positions)) {
            return PuzzleState::initial();
        }

        try {
            return new PuzzleState(array_values($positions));
        } catch (\InvalidArgumentException) {
            return PuzzleState::initial();
        }
    }

    private function readMode(Request $request): PuzzleMode
    {
        $mode = $request->getSession()->get(self::SESSION_MODE_KEY, PuzzleMode::Joint->value);

        return PuzzleMode::tryFrom((string) $mode) ?? PuzzleMode::Joint;
    }
}
