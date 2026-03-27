<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Profile;

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Search\Policy\InquiryableAvailabilityPolicy;
use Skionline\MerlinxGetter\Search\Util\SearchRequestFingerprint;

final class SearchEngineProfile
{
	private ?InquiryableAvailabilityPolicy $inquiryableAvailabilityPolicy = null;
	private ?string $scopeFingerprint = null;

	public function __construct(
		private readonly MerlinxGetterConfig $config,
	) {
	}

	/**
	 * @return array<int, string>
	 */
	public function operators(): array
	{
		return $this->config->searchEngineOperators;
	}

	/**
	 * @return array<int, string>
	 */
	public function childAsAdultOperators(): array
	{
		return $this->config->childAsAdultOperators();
	}

	/**
	 * @return array<int, array{search: array<int, string>, filter: array<int, string>}>
	 */
	public function accommodationAttributeRulesByCondition(): array
	{
		return $this->config->accommodationAttributeRulesByCondition();
	}

	public function inquiryableAvailabilityPolicy(): InquiryableAvailabilityPolicy
	{
		if (!$this->inquiryableAvailabilityPolicy instanceof InquiryableAvailabilityPolicy) {
			$this->inquiryableAvailabilityPolicy = InquiryableAvailabilityPolicy::fromConfig($this->config);
		}

		return $this->inquiryableAvailabilityPolicy;
	}

	public function scopeFingerprint(): string
	{
		if (is_string($this->scopeFingerprint) && $this->scopeFingerprint !== '') {
			return $this->scopeFingerprint;
		}

		$this->scopeFingerprint = SearchRequestFingerprint::hash([
			'schema' => 'search_engine_scope_v1',
			'name' => $this->config->searchEngineName,
			'operators' => $this->config->searchEngineOperators,
			'conditions' => $this->config->searchEngineConditions,
			'availability_policy' => $this->config->searchEngineAvailabilityPolicy,
			'operator_policies' => $this->config->searchEngineOperatorPolicies,
			'response_filters' => $this->config->searchEngineResponseFilters,
			'runtime' => [
				'defaultViewLimit' => $this->config->defaultViewLimit,
			],
		]);

		return $this->scopeFingerprint;
	}

	/**
	 * @param array<string, mixed> $context
	 */
	public function fingerprint(string $schema, array $context = []): string
	{
		return SearchRequestFingerprint::hash([
			'schema' => $schema,
			'engine_scope' => $this->scopeFingerprint(),
			'context' => $context,
		]);
	}
}
