<?php

declare(strict_types=1);

namespace Baraja\GitLabApi;


interface GitLabUser
{
	/** Return current user GitLab token. */
	public function getGitLabToken(): ?string;
}
