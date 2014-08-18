<?php

namespace Stalker\Lib\StbApi;

interface Stb
{
    public function doAuth();

    public function getStorages();

    public function handshake();

    public function log();

    public function setVolume();

    public function setAspect();

    public function getTvAspects();

    public function getLocales();

    public function checkPortalStatus();

    public function getLocalization();

    public function getModules();

    public function getPreloadImages();

    public function setLocale();

    public function getCountries();

    public function getCities();

    public function getTimezones();

    public function searchCities();

    public function getProfile();

    public function getSettingsProfile();

    public function setScreensaverDelay();

    public function setPlasmaSaving();

    public function setTimeshiftSettings();

    public function setHdmiReaction();

    public function setLangPriority();

    public function setPlaybackBuffer();

    public function setSpdifMode();

    public function setPortalPrefs();

    public function setCommonSettings();

    public function setParentPassword();

    public function setPlaybackSettings();

    public function setStreamError();

    public function setClockOnVideo();
}