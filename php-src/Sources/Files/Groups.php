<?php

namespace kalanis\kw_auth_sources\Sources\Files;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Data\FileGroup;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Traits;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class Groups
 * @package kalanis\kw_auth_sources\Sources\Files
 * Work with groups of users
 */
class Groups implements Interfaces\IWorkGroups
{
    use Traits\TAuthLock;
    use Traits\TLines;
    use Traits\TSeparated;
    use Traits\TStatusTransform;

    // default positions
    const GRP_ID = 0;
    const GRP_NAME = 1;
    const GRP_AUTHOR = 2;
    const GRP_DESC = 3;
    const GRP_STATUS = 4;
    const GRP_PARENTS = 5;
    const GRP_EXTRA = 6;
    const GRP_FEED = 7;

    /** @var Storages\AStorage */
    protected $storage = null;
    /** @var Interfaces\IWorkAccounts */
    protected $accounts = null;
    /** @var Interfaces\IExtraParser */
    protected $extraParser = null;
    /** @var string[] */
    protected $path = [];

    /**
     * @param Storages\AStorage $storage
     * @param Interfaces\IWorkAccounts $accounts
     * @param Interfaces\IExtraParser $parser
     * @param ILock $lock
     * @param string[] $path
     * @param Interfaces\IKAusTranslations|null $lang
     */
    public function __construct(
        Storages\AStorage $storage,
        Interfaces\IWorkAccounts $accounts,
        Interfaces\IExtraParser $parser,
        ILock $lock,
        array $path,
        ?Interfaces\IKAusTranslations $lang = null
    )
    {
        $this->setAusLang($lang);
        $this->initAuthLock($lock);
        $this->storage = $storage;
        $this->path = $path;
        $this->accounts = $accounts;
        $this->extraParser = $parser;
    }

    public function createGroup(Interfaces\IGroup $group): bool
    {
        $userId = $group->getGroupAuthorId();
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        // not everything necessary is set
        if (empty($userId) || empty($groupName)) {
            throw new AuthSourcesException($this->getAusLang()->kauGroupMissParam());
        }
        $this->checkLock();

        $gid = 0;
        $this->getLock()->create();

        // read groups
        try {
            $groupLines = $this->openGroups();
        } catch (AuthSourcesException $ex) {
            // silence the problems on storage
            $groupLines = [];
        }
        foreach ($groupLines as &$line) {
            $gid = max($gid, $line[static::GRP_ID]);
        }
        $gid++;

        $newGroup = [
            static::GRP_ID => $gid,
            static::GRP_NAME => $groupName,
            static::GRP_AUTHOR => $userId,
            static::GRP_DESC => !empty($groupDesc) ? $groupDesc : $groupName,
            static::GRP_STATUS => $group->getGroupStatus(),
            static::GRP_PARENTS => $this->compactStr($group->getGroupParents()),
            static::GRP_EXTRA => $this->extraParser->compact($group->getGroupExtra()),
            static::GRP_FEED => '',
        ];
        ksort($newGroup);
        $groupLines[] = $newGroup;

        try {
            // now save it
            $result = $this->saveGroups($groupLines);
        } finally {
            $this->getLock()->delete();
        }
        return $result;
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        $this->checkLock();
        try {
            $groupLines = $this->openGroups();
        } catch (AuthSourcesException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($groupLines as &$line) {
            if ($line[static::GRP_ID] == $groupId) {
                return $this->getGroupClass($line);
            }
        }
        return null;
    }

    public function readGroup(): array
    {
        $this->checkLock();

        $groupLines = $this->openGroups();
        $result = [];
        foreach ($groupLines as &$line) {
            $result[] = $this->getGroupClass($line);
        }

        return $result;
    }

    /**
     * @param array<int, string> $line
     * @throws AuthSourcesException
     * @return Interfaces\IGroup
     */
    protected function getGroupClass(array &$line): Interfaces\IGroup
    {
        $group = new FileGroup();
        $group->setGroupData(
            strval($line[static::GRP_ID]),
            strval($line[static::GRP_NAME]),
            strval($line[static::GRP_DESC]),
            strval($line[static::GRP_AUTHOR]),
            intval($line[static::GRP_STATUS]),
            $this->separateStr($line[static::GRP_PARENTS]),
            $this->extraParser->expand($line[static::GRP_EXTRA])
        );
        return $group;
    }

    /**
     * @param Interfaces\IGroup $group
     * @throws LockException
     * @throws AuthSourcesException
     * @return bool
     */
    public function updateGroup(Interfaces\IGroup $group): bool
    {
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        $this->checkLock();

        $this->getLock()->create();
        try {
            $groupLines = $this->openGroups();
        } finally {
            $this->getLock()->delete();
        }
        foreach ($groupLines as &$line) {
            if ($line[static::GRP_ID] == $group->getGroupId()) {
                // REFILL
                $line[static::GRP_NAME] = !empty($groupName) ? $groupName : $line[static::GRP_NAME] ;
                $line[static::GRP_DESC] = !empty($groupDesc) ? $groupDesc : $line[static::GRP_DESC] ;
                $line[static::GRP_STATUS] = $group->getGroupStatus();
                $line[static::GRP_PARENTS] = $this->compactStr($group->getGroupParents());
                $line[static::GRP_EXTRA] = $this->extraParser->compact($group->getGroupExtra());
            }
        }

        try {
            $result = $this->saveGroups($groupLines);
        } finally {
            $this->getLock()->delete();
        }
        return $result;
    }

    /**
     * @param string $groupId
     * @throws LockException
     * @throws AuthSourcesException
     * @return bool
     */
    public function deleteGroup(string $groupId): bool
    {
        $this->checkLock();
        $this->checkRest($groupId);

        $changed = false;
        $this->getLock()->create();

        // update groups
        try {
            $openGroups = $this->openGroups();
        } catch (AuthSourcesException $ex) {
            // silence the problems on storage
            $this->getLock()->delete();
            return false;
        }
        foreach ($openGroups as $index => &$line) {
            if ($line[static::GRP_ID] == $groupId) {
                unset($openGroups[$index]);
                $changed = true;
            }
        }

        $result = true;
        try {
            // now save it
            if ($changed) {
                $result = $this->saveGroups($openGroups);
            }
        } finally {
            $this->getLock()->delete();
        }
        return $changed && $result;
    }

    /**
     * @param string $groupId
     * @throws AuthSourcesException
     * @throws LockException
     */
    protected function checkRest(string $groupId): void
    {
        $passLines = $this->accounts->readAccounts();
        foreach ($passLines as &$line) {
            if ($line->getGroup() == $groupId) {
                throw new AuthSourcesException($this->getAusLang()->kauGroupHasMembers());
            }
        }
    }

    /**
     * @throws AuthSourcesException
     * @return array<int, array<int, string|int>>
     */
    protected function openGroups(): array
    {
        return $this->storage->read(array_merge($this->path, [Interfaces\IFile::GROUP_FILE]));
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthSourcesException
     * @return bool
     */
    protected function saveGroups(array $lines): bool
    {
        return $this->storage->write(array_merge($this->path, [Interfaces\IFile::GROUP_FILE]), $lines);
    }

    /**
     * @return string
     * @codeCoverageIgnore translation
     */
    protected function noDirectoryDelimiterSet(): string
    {
        return $this->getAusLang()->kauNoDelimiterSet();
    }
}
