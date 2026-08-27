<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Tests;

use IntlException;
use MessageFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Every catalogue must cover the reference locale's messages, each in a wording of its own. Neither a
 * missing catalogue nor an untranslated message fails at runtime, so this test is what guards them.
 */
class TranslationCatalogueTest extends TestCase
{
    private const string TRANSLATIONS_DIR = __DIR__ . '/../translations';
    private const string DOMAIN = 'assoconnect_smtp_toolbox+intl-icu';
    private const string REFERENCE_LOCALE = 'en';

    /** Every locale the catalogues are expected to cover. */
    private const array EXPECTED_LOCALES = ['en', 'fr', 'es'];

    /** @return iterable<string, array{locale: string}> */
    public static function provideLocales(): iterable
    {
        foreach (self::EXPECTED_LOCALES as $locale) {
            yield $locale => ['locale' => $locale];
        }
    }

    #[DataProvider('provideLocales')]
    public function testCatalogueExists(string $locale): void
    {
        self::assertFileExists(self::cataloguePath($locale));
    }

    #[DataProvider('provideLocales')]
    public function testCatalogueCoversEveryReferenceMessage(string $locale): void
    {
        self::assertSame(
            array_keys($this->messages(self::REFERENCE_LOCALE)),
            array_keys($this->messages($locale)),
            sprintf('The %s catalogue does not cover the same messages as the %s one.', $locale, self::REFERENCE_LOCALE)
        );
    }

    #[DataProvider('provideLocales')]
    public function testEveryMessageIsTranslated(string $locale): void
    {
        $messages = $this->messages($locale);

        foreach ($this->messages(self::REFERENCE_LOCALE) as $key => $reference) {
            $message = trim($messages[$key] ?? '');

            // Some messages are deliberately blank, so emptiness is only a gap where the reference has wording
            if ('' === trim($reference)) {
                self::assertSame('', $message, sprintf('Message "%s" should stay blank.', $key));
                continue;
            }

            self::assertNotSame('', $message, sprintf('Message "%s" has no %s translation.', $key, $locale));

            if (self::REFERENCE_LOCALE === $locale) {
                continue;
            }

            // A catalogue copied from the reference locale and left untranslated clears the emptiness check
            self::assertNotSame(
                trim($reference),
                $message,
                sprintf('Message "%s" still reads as its %s wording.', $key, self::REFERENCE_LOCALE)
            );
        }
    }

    /**
     * The domain carries the +intl-icu suffix, so every message is an ICU pattern. `{{ name }}` is a
     * syntax error there, and the translator throws on render rather than at load time.
     */
    #[DataProvider('provideLocales')]
    public function testEveryMessageIsAValidIcuPattern(string $locale): void
    {
        $invalid = [];

        foreach ($this->messages($locale) as $key => $message) {
            if ('' === trim($message)) {
                continue;
            }

            try {
                new MessageFormatter($locale, $message);
            } catch (IntlException $exception) {
                $invalid[] = sprintf('%s (%s)', $key, $exception->getMessage());
            }
        }

        self::assertSame([], $invalid, sprintf('Invalid ICU pattern(s) in the %s catalogue.', $locale));
    }

    #[DataProvider('provideLocales')]
    public function testEveryMessageCarriesTheReferencePlaceholders(string $locale): void
    {
        $messages = $this->messages($locale);

        foreach ($this->messages(self::REFERENCE_LOCALE) as $key => $reference) {
            self::assertSame(
                self::placeholders($reference),
                self::placeholders($messages[$key] ?? ''),
                sprintf('Message "%s" does not carry the same placeholders in %s.', $key, $locale)
            );
        }
    }

    private static function cataloguePath(string $locale): string
    {
        return sprintf('%s/%s.%s.yml', self::TRANSLATIONS_DIR, self::DOMAIN, $locale);
    }

    /** @return array<string, string> */
    private function messages(string $locale): array
    {
        $catalogue = Yaml::parseFile(self::cataloguePath($locale));
        self::assertIsArray($catalogue, sprintf('The %s catalogue is not a YAML mapping.', $locale));

        $messages = self::flatten($catalogue);
        ksort($messages);

        return $messages;
    }

    /**
     * @param array<mixed> $catalogue
     *
     * @return array<string, string>
     */
    private static function flatten(array $catalogue, string $prefix = ''): array
    {
        $messages = [];

        foreach ($catalogue as $key => $value) {
            $path = '' === $prefix ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $messages += self::flatten($value, $path);
                continue;
            }

            $messages[$path] = (string) $value;
        }

        return $messages;
    }

    /** @return list<string> */
    private static function placeholders(string $message): array
    {
        preg_match_all('/\{(\w+)\}/', $message, $matches);
        $placeholders = array_unique($matches[1]);
        sort($placeholders);

        return $placeholders;
    }
}
