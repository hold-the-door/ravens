<?php

use Hodor\Database\Phpmig\Migration;
use Lstr\YoPdo\YoPdo;

class CreateInitialSchema extends Migration
{
    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalUp(YoPdo $yo_pdo)
    {
        $sql = <<<SQL
CREATE TABLE buffered_jobs
(
    buffered_job_id SERIAL PRIMARY KEY,
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL
);

CREATE TABLE queued_jobs
(
    queued_job_id SERIAL PRIMARY KEY,
    buffered_job_id INT NOT NULL UNIQUE,
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL,
    superqueued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    superqueued_from VARCHAR NOT NULL
);

CREATE TABLE successful_jobs
(
    successful_job_id SERIAL PRIMARY KEY,
    buffered_job_id INT NOT NULL UNIQUE,
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL,
    superqueued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    superqueued_from VARCHAR NOT NULL,
    started_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    finished_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    ran_from VARCHAR NOT NULL,
    dequeued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    dequeued_from VARCHAR NOT NULL
);

CREATE TABLE failed_jobs
(
    failed_job_id SERIAL PRIMARY KEY,
    buffered_job_id INT NOT NULL UNIQUE,
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL,
    superqueued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    superqueued_from VARCHAR NOT NULL,
    started_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    finished_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    ran_from VARCHAR NOT NULL,
    dequeued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    dequeued_from VARCHAR NOT NULL
);
SQL;

        $yo_pdo->queryMultiple($sql);
    }

    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalDown(YoPdo $yo_pdo)
    {
        $sql = <<<SQL
DROP TABLE buffered_jobs;
DROP TABLE queued_jobs;
DROP TABLE successful_jobs;
DROP TABLE failed_jobs;
SQL;

        $yo_pdo->queryMultiple($sql);
    }
}
