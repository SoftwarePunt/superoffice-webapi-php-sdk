<?php

namespace roydejong\SoWebApi\Structs\Appointments;

use roydejong\SoWebApi\Structs\JsonStruct;

/**
 * @see https://community.superoffice.com/documentation/sdk/SO.NetServer.Web.Services/html/v1DocumentEntity_GetAll.htm
 */
class ODataDocumentItem extends JsonStruct
{
    public string $visibleFor;
    public int $documentId;
    public string $type;
    public \DateTime $date;
    public string $associateId;
    public int $contactId;
    public int $personId;
    public int $projectId;
    public int $saleId;
    public string $userGroup;
    public string $keywords;
    public string $ourref;
    public string $yourref;
    public string $attention;
    public string $subject;
    public int $snum;
    public int $suggestedDocumentId;
    public string $registeredBy;
    public \DateTime $registeredDate;
    public bool $completed;
    public string $icon;
    public string $text;
    public \DateTime $time;
    public bool $isReport;
    public bool $isMail;
    public bool $mailMergeDraft;
    public string $updatedBy;
    public \DateTime $updatedDate;
    public string $recordType;
    public string $who;
}