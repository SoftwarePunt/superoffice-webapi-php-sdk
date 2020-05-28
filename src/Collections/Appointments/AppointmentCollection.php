<?php

namespace roydejong\SoWebApi\Collections\Appointments;

use roydejong\SoWebApi\Collections\Collection;
use roydejong\SoWebApi\Structs\Appointments\AppointmentEntity;
use roydejong\SoWebApi\Structs\JsonStruct;
use roydejong\SoWebApi\WebApiException;

class AppointmentCollection extends Collection
{
    public static string $PATH = "/api/v1/Appointment";

    /**
     * Gets a AppointmentEntity object by id.
     *
     * @see https://community.superoffice.com/documentation/sdk/SO.NetServer.Web.Services/html/v1AppointmentEntity_GetAppointmentEntity.htm
     *
     * @param int $id The id of the AppointmentEntity to return.
     * @return AppointmentEntity|JsonStruct
     * @throws WebApiException
     */
    public function getById(int $id): AppointmentEntity
    {
        return AppointmentEntity::fromResponse(
            $this->client->get(self::$PATH . "/{$id}")
        );
    }
}