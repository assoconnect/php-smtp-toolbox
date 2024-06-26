<?php

declare(strict_types=1);

namespace AssoConnect\SmtpToolbox\Translatable;

use AssoConnect\SmtpToolbox\Resolver\BounceTypeResolver;
use AssoConnect\SmtpToolbox\Specification\BounceCauseSpecificationInterface;
use AssoConnect\SmtpToolbox\Specification\BounceIsCausedByInactiveUserSpecification;
use AssoConnect\SmtpToolbox\Specification\BounceIsCausedByOverQuotaSpecification;
use AssoConnect\SmtpToolbox\Specification\BounceIsCausedByUnknownUserSpecification;
use AssoConnect\SmtpToolbox\Specification\BounceIsSpamRelatedSpecification;
use AssoConnect\SmtpToolbox\Specification\BounceReasonIsUnknownSpecification;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BounceReasonTranslatable implements TranslatableInterface
{
    /** @var string[] */
    private array $translationKeys;

    public function __construct(string $reason)
    {
        $this->translationKeys = $this->getTranslationKey($reason);
    }

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return implode('', array_filter([
            $translator->trans('bounce.reason.' . $this->translationKeys[0], [], 'assoconnect_smtp_toolbox'),
            $translator->trans('bounce.tips.' . $this->translationKeys[1], [], 'assoconnect_smtp_toolbox'),
        ]));
    }

    /** @return string[] */
    private function getTranslationKey(string $reason): array
    {
        /** @var array<class-string<BounceCauseSpecificationInterface>, string[]> $map */
        $map = [
            BounceIsCausedByInactiveUserSpecification::class => ['inactive', 'contact'],
            BounceIsCausedByOverQuotaSpecification::class => ['quota', 'contact'],
            BounceIsCausedByUnknownUserSpecification::class => ['unknown_user', 'review'],
            BounceIsSpamRelatedSpecification::class => ['spam', 'none'],
            BounceReasonIsUnknownSpecification::class => ['unknown', 'none'],
        ];

        foreach ($map as $specificationClass => $translationKeys) {
            if (BounceIsSpamRelatedSpecification::class === $specificationClass) {
                $specification = new $specificationClass(new BounceTypeResolver());
            } else {
                $specification = new $specificationClass();
            }
            if ($specification->isSatisfiedBy($reason)) {
                return $translationKeys;
            }
        }

        return ['none', 'none'];
    }
}
