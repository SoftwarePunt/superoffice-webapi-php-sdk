<?php

namespace SoftwarePunt\SoWebApi\Collections\Appointments;

use SoftwarePunt\SoWebApi\Collections\Collection;
use SoftwarePunt\SoWebApi\Structs\Appointments\AppointmentEntity;
use SoftwarePunt\SoWebApi\Structs\JsonStruct;
use SoftwarePunt\SoWebApi\WebApiException;

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