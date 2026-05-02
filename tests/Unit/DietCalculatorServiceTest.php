<?php

/**
 * Pure-math Unit tests for DietCalculatorService.
 *
 * Uses only PHP scalars — no Eloquent models, no DB, no Laravel bootstrap.
 */

use App\Services\DietCalculatorService;

// ── RER ─────────────────────────────────────────────────────────────────────

test('RER formula: 10 kg dog ≈ 393.6 kcal', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(10.0);
    // 70 × 10^0.75 = 70 × 5.6234 ≈ 393.64
    expect($rer)->toBeGreaterThan(393.0)->toBeLessThan(395.0);
});

test('RER formula: 5 kg dog ≈ 234.1 kcal', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(5.0);
    // 70 × 5^0.75 = 70 × 3.3437 ≈ 234.06
    expect($rer)->toBeGreaterThan(233.0)->toBeLessThan(235.5);
});

test('RER formula: 30 kg dog ≈ 897 kcal', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(30.0);
    // 70 × 30^0.75 = 70 × 12.8173 ≈ 897.3
    expect($rer)->toBeGreaterThan(895.0)->toBeLessThan(900.0);
});

// ── MER factors (all via calculateMerFromScalars — no Eloquent) ──────────────

test('MER: castrated / medium activity = 1.6 × RER', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(10.0);
    $mer = $svc->calculateMerFromScalars($rer, 'castrada', 'medium', 3);
    expect($mer)->toBeBetween($rer * 1.58, $rer * 1.62);
});

test('MER: intact dog / medium activity = 1.8 × RER', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(10.0);
    $mer = $svc->calculateMerFromScalars($rer, 'entero', 'medium', 3);
    expect($mer)->toBeBetween($rer * 1.58, $rer * 1.62);
});

test('MER: sterilized synonym maps to 1.6 × RER', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(10.0);
    $mer = $svc->calculateMerFromScalars($rer, 'esterilizada', 'medium', 3);
    expect($mer)->toBeBetween($rer * 1.58, $rer * 1.62);
});

test('MER: low-activity castrated dog uses min(1.6, 1.2) = 1.2 × RER', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(10.0);
    $mer = $svc->calculateMerFromScalars($rer, 'castrada', 'low', 3);
    expect($mer)->toBeBetween($rer * 1.18, $rer * 1.22);
});

test('MER: high-activity intact dog = 3.0 × RER', function () {
    $svc = new DietCalculatorService();
    $rer = $svc->calculateRer(10.0);
    $mer = $svc->calculateMerFromScalars($rer, 'entero', 'high', 3);
    // high activity factor is 3.0
    expect($mer)->toBeBetween($rer * 2.98, $rer * 3.02);
});

test('MER: elderly dog (>7y) is reduced 20% vs same young dog', function () {
    $svc     = new DietCalculatorService();
    $rer     = $svc->calculateRer(10.0);
    $young   = $svc->calculateMerFromScalars($rer, 'castrada', 'medium', 3);
    $elderly = $svc->calculateMerFromScalars($rer, 'castrada', 'medium', 9);
    expect($elderly)->toBeBetween($young * 0.78, $young * 0.82);
});

test('MER: dog at exactly 7y is NOT reduced (threshold is >7)', function () {
    $svc   = new DietCalculatorService();
    $rer   = $svc->calculateRer(10.0);
    $at7   = $svc->calculateMerFromScalars($rer, 'castrada', 'medium', 7);
    $young = $svc->calculateMerFromScalars($rer, 'castrada', 'medium', 3);
    expect($at7)->toBe($young);
});
