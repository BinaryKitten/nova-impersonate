<?php

namespace KABBOUCHI\NovaImpersonate\Http\Middleware;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Lab404\Impersonate\Services\ImpersonateManager;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Impersonate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        $response = $next($request);

        /** @var ImpersonateManager $manager */
        $manager = app()->make(ImpersonateManager::class);

        if (
            $manager->isImpersonating() &&

            auth()->check() &&

            !($response instanceof RedirectResponse) &&

            !($response instanceof BinaryFileResponse) &&

            !($response instanceof StreamedResponse) &&

            !($response instanceof JsonResponse) &&

            !$request->expectsJson() &&

            Str::startsWith($response->headers->get('Content-Type'), 'text/html') &&

            !Str::contains($request->path(), 'nova-api')
        ) {

            /** @var Response $response * */
            $content = $response->getContent();

            $content .= view('nova-impersonate::reverse')->render();

            $response->setContent($content);
        }

        return $response;
    }
}
