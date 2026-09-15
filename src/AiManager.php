<?php

namespace Ugarit\Ai;

use Heritage\Contracts\Events\Dispatcher;
use Heritage\Support\MultipleInstanceManager;
use InvalidArgumentException;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Providers\AudioProvider;
use Ugarit\Ai\Contracts\Providers\EmbeddingProvider;
use Ugarit\Ai\Contracts\Providers\FileProvider;
use Ugarit\Ai\Contracts\Providers\ImageProvider;
use Ugarit\Ai\Contracts\Providers\RerankingProvider;
use Ugarit\Ai\Contracts\Providers\StoreProvider;
use Ugarit\Ai\Contracts\Providers\TextProvider;
use Ugarit\Ai\Contracts\Providers\TranscriptionProvider;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Gateway\Anthropic\AnthropicGateway;
use Ugarit\Ai\Gateway\Gemini\GeminiGateway;
use Ugarit\Ai\Gateway\OpenAi\OpenAiGateway;
use Ugarit\Ai\Providers\AnthropicProvider;
use Ugarit\Ai\Providers\AzureOpenAiProvider;
use Ugarit\Ai\Providers\BedrockProvider;
use Ugarit\Ai\Providers\CohereProvider;
use Ugarit\Ai\Providers\DeepSeekProvider;
use Ugarit\Ai\Providers\ElevenLabsProvider;
use Ugarit\Ai\Providers\GeminiProvider;
use Ugarit\Ai\Providers\GroqProvider;
use Ugarit\Ai\Providers\JinaProvider;
use Ugarit\Ai\Providers\MistralProvider;
use Ugarit\Ai\Providers\OllamaProvider;
use Ugarit\Ai\Providers\OpenAiCompatibleProvider;
use Ugarit\Ai\Providers\OpenAiProvider;
use Ugarit\Ai\Providers\OpenRouterProvider;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Providers\VoyageAiProvider;
use Ugarit\Ai\Providers\XaiProvider;
use LogicException;

class AiManager extends MultipleInstanceManager
{
    use Concerns\InteractsWithFakeAgents;
    use Concerns\InteractsWithFakeAudio;
    use Concerns\InteractsWithFakeEmbeddings;
    use Concerns\InteractsWithFakeFiles;
    use Concerns\InteractsWithFakeImages;
    use Concerns\InteractsWithFakeReranking;
    use Concerns\InteractsWithFakeStores;
    use Concerns\InteractsWithFakeTranscriptions;

    /**
     * The key name of the "driver" equivalent configuration option.
     *
     * @var string
     */
    protected $driverKey = 'driver';

    /**
     * Get a provider instance by name.
     *
     * @throws LogicException
     */
    public function audioProvider(?string $name = null): AudioProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof AudioProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support audio generation.');
            }
        });
    }

    /**
     * Get an audio provider instance, using a fake gateway if audio is faked.
     *
     * @throws LogicException
     */
    public function fakeableAudioProvider(?string $name = null): AudioProvider
    {
        $provider = $this->audioProvider($name);

        return $this->audioIsFaked()
            ? (clone $provider)->useAudioGateway($this->fakeAudioGateway())
            : $provider;
    }

    /**
     * Get a provider instance by name.
     *
     * @throws LogicException
     */
    public function embeddingProvider(?string $name = null): EmbeddingProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof EmbeddingProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support embedding generation.');
            }
        });
    }

    /**
     * Get an embedding provider instance, using a fake gateway if embeddings are faked.
     *
     * @throws LogicException
     */
    public function fakeableEmbeddingProvider(?string $name = null): EmbeddingProvider
    {
        $provider = $this->embeddingProvider($name);

        return $this->embeddingsAreFaked()
            ? (clone $provider)->useEmbeddingGateway($this->fakeEmbeddingGateway())
            : $provider;
    }

    /**
     * Get a reranking provider instance by name.
     *
     * @throws LogicException
     */
    public function rerankingProvider(?string $name = null): RerankingProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof RerankingProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support reranking.');
            }
        });
    }

    /**
     * Get a reranking provider instance, using a fake gateway if reranking is faked.
     *
     * @throws LogicException
     */
    public function fakeableRerankingProvider(?string $name = null): RerankingProvider
    {
        $provider = $this->rerankingProvider($name);

        return $this->rerankingIsFaked()
            ? (clone $provider)->useRerankingGateway($this->fakeRerankingGateway())
            : $provider;
    }

    /**
     * Get a provider instance by name.
     *
     * @throws LogicException
     */
    public function imageProvider(?string $name = null): ImageProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof ImageProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support image generation.');
            }
        });
    }

    /**
     * Get an image provider instance, using a fake gateway if images are faked.
     *
     * @throws LogicException
     */
    public function fakeableImageProvider(?string $name = null): ImageProvider
    {
        $provider = $this->imageProvider($name);

        return $this->imagesAreFaked()
            ? (clone $provider)->useImageGateway($this->fakeImageGateway())
            : $provider;
    }

    /**
     * Get a provider instance by name.
     *
     * @throws LogicException
     */
    public function textProvider(?string $name = null): TextProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof TextProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support text generation.');
            }
        });
    }

    /**
     * Get a provider instance for an agent by name.
     *
     * @throws LogicException
     */
    public function textProviderFor(Agent $agent, ?string $name = null): TextProvider
    {
        $provider = $this->textProvider($name);

        return $this->hasFakeGatewayFor($agent)
            ? (clone $provider)->useTextGateway($this->fakeGatewayFor($agent))
            : $provider;
    }

    /**
     * Get a provider instance by name.
     *
     * @throws LogicException
     */
    public function transcriptionProvider(?string $name = null): TranscriptionProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof TranscriptionProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support transcription generation.');
            }
        });
    }

    /**
     * Get a transcription provider instance, using a fake gateway if transcriptions are faked.
     *
     * @throws LogicException
     */
    public function fakeableTranscriptionProvider(?string $name = null): TranscriptionProvider
    {
        $provider = $this->transcriptionProvider($name);

        return $this->transcriptionsAreFaked()
            ? (clone $provider)->useTranscriptionGateway($this->fakeTranscriptionGateway())
            : $provider;
    }

    /**
     * Get a file provider instance by name.
     *
     * @throws LogicException
     */
    public function fileProvider(?string $name = null): FileProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof FileProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support file management.');
            }
        });
    }

    /**
     * Get a file provider instance, using a fake gateway if files are faked.
     *
     * @throws LogicException
     */
    public function fakeableFileProvider(?string $name = null): FileProvider
    {
        $provider = $this->fileProvider($name);

        return $this->filesAreFaked()
            ? (clone $provider)->useFileGateway($this->fakeFileGateway())
            : $provider;
    }

    /**
     * Get a store provider instance by name.
     *
     * @throws LogicException
     */
    public function storeProvider(?string $name = null): StoreProvider
    {
        return tap($this->instance($name), function ($instance): void {
            if (! $instance instanceof StoreProvider) {
                throw new LogicException('Provider ['.$instance::class.'] does not support store management.');
            }
        });
    }

    /**
     * Get a store provider instance, using a fake gateway if stores are faked.
     *
     * @throws LogicException
     */
    public function fakeableStoreProvider(?string $name = null): StoreProvider
    {
        $provider = $this->storeProvider($name);

        return $this->storesAreFaked()
            ? (clone $provider)->useStoreGateway($this->fakeStoreGateway())
            : $provider;
    }

    /**
     * Create an Anthropic powered instance.
     */
    public function createAnthropicDriver(array $config): AnthropicProvider
    {
        return new AnthropicProvider(
            new AnthropicGateway($this->app['events']),
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an Azure OpenAI powered instance.
     */
    public function createAzureDriver(array $config): AzureOpenAiProvider
    {
        return new AzureOpenAiProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an AWS Bedrock powered instance.
     */
    public function createBedrockDriver(array $config): BedrockProvider
    {
        return new BedrockProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create a Cohere powered instance.
     */
    public function createCohereDriver(array $config): CohereProvider
    {
        return new CohereProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create a DeepSeek powered instance.
     */
    public function createDeepseekDriver(array $config): DeepSeekProvider
    {
        return new DeepSeekProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an ElevenLabs powered instance.
     */
    public function createElevenDriver(array $config): ElevenLabsProvider
    {
        return new ElevenLabsProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create a Gemini powered instance.
     */
    public function createGeminiDriver(array $config): GeminiProvider
    {
        return new GeminiProvider(
            new GeminiGateway($this->app['events']),
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create a Groq powered instance.
     */
    public function createGroqDriver(array $config): GroqProvider
    {
        return new GroqProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create a Jina powered instance.
     */
    public function createJinaDriver(array $config): JinaProvider
    {
        return new JinaProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create a Mistral AI powered instance.
     */
    public function createMistralDriver(array $config): MistralProvider
    {
        return new MistralProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an Ollama powered instance.
     */
    public function createOllamaDriver(array $config): OllamaProvider
    {
        return new OllamaProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an OpenAI powered instance.
     */
    public function createOpenaiDriver(array $config): OpenAiProvider
    {
        return new OpenAiProvider(
            new OpenAiGateway($this->app['events']),
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an OpenAI-compatible powered instance.
     */
    public function createOpenaiCompatibleDriver(array $config): OpenAiCompatibleProvider
    {
        return new OpenAiCompatibleProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an OpenRouter powered instance.
     */
    public function createOpenrouterDriver(array $config): OpenRouterProvider
    {
        return new OpenRouterProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create a VoyageAI powered instance.
     */
    public function createVoyageaiDriver(array $config): VoyageAiProvider
    {
        return new VoyageAiProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Create an xAI powered instance.
     */
    public function createXaiDriver(array $config): XaiProvider
    {
        return new XaiProvider(
            $config,
            $this->app->make(Dispatcher::class),
        );
    }

    /**
     * Get the default instance name.
     */
    public function getDefaultInstance(): string
    {
        $default = $this->app['config']['ai.default'];

        if ($default instanceof Lab) {
            return $default->value;
        }

        if (is_array($default)) {
            throw new InvalidArgumentException('The "ai.default" config value must be a string provider name or a Lab enum, not an array.');
        }

        return $default;
    }

    /**
     * Set the default instance name.
     *
     * @param  string  $name
     */
    public function setDefaultInstance($name): void
    {
        $this->app['config']['ai.default'] = $name;
    }

    /**
     * Get the instance specific configuration.
     *
     * @param  string  $name
     */
    public function getInstanceConfig($name): array
    {
        $config = $this->app['config']->get(
            'ai.providers.'.$name, ['driver' => $name],
        );

        if ($config['driver'] instanceof Lab) {
            $config['driver'] = $config['driver']->value;
        }

        $config['name'] = $name;

        return $config;
    }
}
