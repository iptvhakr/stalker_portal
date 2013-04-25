<?php

namespace Stalker\Lib\StbApi;

interface RemotePvr
{
    public function startRecNow();

    public function startRecDeferred();

    public function startDeferredRecordOnStb();

    public function startRecordOnStb();

    public function stopRec();

    public function stopRecordOnStb();

    public function stopRecDeferred();

    public function delRec();

    public function delRecordOnStb();

    public function createLink();

    public function updateRecordOnStbEndTime();

    public function setInternalId();

    public function getActiveRecordings();

    public function getOrderedList();
}