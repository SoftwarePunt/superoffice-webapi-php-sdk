<?php

namespace roydejong\SoWebApi\Structs\Appointments;

use roydejong\SoWebApi\Structs\JsonStruct;

/**
 * @see https://community.superoffice.com/documentation/sdk/SO.NetServer.Web.Services/html/v1AppointmentEntity_GetAll.htm
 */
class ODataAppointmentItem extends JsonStruct
{
    public string $visibleFor;
    public string $type;
    public string $priority;
    public \DateTime $date;
    public \DateTime $dateEnd;
    public string $associateId;
    public int $appointmentId;
    public int $contactId;
    public int $personId;
    public int $projectId;
    public int $saleId;
    public string $userGroup;
    public int $recurrenceRuleId;
    public int $rawStatus;
    public int $rawType;
    public int $invitedPersonId;
    public int $cautionWarning;
    public string $location;
    public int $suggestedAppointmentId;
    public string $intention;
    public bool $isMilestone;
    public string $registeredBy;
    public \DateTime $registeredDate;
    public bool $completed;
    public \DateTime $completedDate;
    public string $icon;
    public string $recordType;
    public \DateTime $time;
    public \DateTime $endTime;
    public string $text;
    public bool $alarm;
    public bool $recurring;
    public bool $booking;
    public bool $visibleInDiary;
    public string $updatedBy;
    public \DateTime $updatedDate;
    public string $who;
}