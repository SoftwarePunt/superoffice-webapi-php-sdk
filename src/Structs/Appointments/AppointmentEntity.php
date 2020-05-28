<?php

namespace roydejong\SoWebApi\Structs\Appointments;

use roydejong\SoWebApi\Structs\JsonStruct;

/**
 * @see https://community.superoffice.com/documentation/sdk/SO.NetServer.Web.Services/html/v1AppointmentEntity_GetAppointmentEntity.htm
 */
class AppointmentEntity extends JsonStruct
{
    public \DateTime $CreatedDate;
    public int $AppointmentId;
    public string $Description;
    public \DateTime $StartDate;
    public \DateTime $EndDate;
}