<?php

class AuthOpenIDSessionStore extends Auth_OpenID_OpenIDStore {

    /**
     * Store association until its expiration time in memcached. 
     * Overwrites any existing association with same server_url and 
     * handle. Handles list of associations for every server. 
     */
    function storeAssociation($server_url, $association)
    {
        // create memcached keys for association itself 
        // and list of associations for this server
        $associationKey = $this->associationKey($server_url, 
            $association->handle);
        $serverKey = $this->associationServerKey($server_url);
        
        // get list of associations 
        $serverAssociations = unserialize(DbSession::get($serverKey));
        
        // if no such list, initialize it with empty array
        if (!$serverAssociations) {
            $serverAssociations = array();
        }
        // and store given association key in it
        $serverAssociations[$association->issued] = $associationKey;
        
        // save associations' keys list 
        DbSession::set($serverKey, serialize($serverAssociations));
        // save association itself
        DbSession::set($associationKey, serialize($association)); 
    }

    /**
     * Read association from memcached. If no handle given 
     * and multiple associations found, returns latest issued
     */
    function getAssociation($server_url, $handle = null)
    {
        // simple case: handle given
        if ($handle !== null) {
            // get association, return null if failed
            $association = unserialize(DbSession::get(
                $this->associationKey($server_url, $handle)));
            return $association ? $association : null;
        }
        
        // no handle given, working with list
        // create key for list of associations
        $serverKey = $this->associationServerKey($server_url);
        
        // get list of associations
        $serverAssociations = unserialize(DbSession::get($serverKey));
        // return null if failed or got empty list
        if (!$serverAssociations) {
            return null;
        }
        
        // get key of most recently issued association
        $keys = array_keys($serverAssociations);
        sort($keys);
        $lastKey = $serverAssociations[array_pop($keys)];
        
        // get association, return null if failed
        $association = unserialize(DbSession::get($lastKey));
        return $association ? $association : null;
    }

    /**
     * Immediately delete association from memcache.
     */
    function removeAssociation($server_url, $handle)
    {
        // create memcached keys for association itself 
        // and list of associations for this server
        $serverKey = $this->associationServerKey($server_url);
        $associationKey = $this->associationKey($server_url, 
            $handle);
        
        // get list of associations
        $serverAssociations = unserialize(DbSession::get($serverKey));
        // return null if failed or got empty list
        if (!$serverAssociations) {
            return false;
        }
        
        // ensure that given association key exists in list
        $serverAssociations = array_flip($serverAssociations);
        if (!array_key_exists($associationKey, $serverAssociations)) {
            return false;
        }
        
        // remove given association key from list
        unset($serverAssociations[$associationKey]);
        $serverAssociations = array_flip($serverAssociations);

        // save updated list
        DbSession::set(
            $serverKey,
            serialize($serverAssociations)
        );

        // delete association 
        return DbSession::delete($associationKey);
    }

    /**
     * Create nonce for server and salt, expiring after 
     * $Auth_OpenID_SKEW seconds.
     */
    function useNonce($server_url, $timestamp, $salt)
    {
        global $Auth_OpenID_SKEW;
        
        // save one request to memcache when nonce obviously expired 
        if (abs($timestamp - time()) > $Auth_OpenID_SKEW) {
            return false;
        }
        
        // returns false when nonce already exists
        // otherwise adds nonce
        if (DbSession::get('openid_nonce_' . sha1($server_url) . '_' . sha1($salt))) {
            return false;
        }
        DbSession::set(
            'openid_nonce_' . sha1($server_url) . '_' . sha1($salt), 
            1 // any value here 
        );

        return true;
    }
    
    /**
     * Memcache key is prefixed with 'openid_association_' string. 
     */
    function associationKey($server_url, $handle = null) 
    {
        return 'openid_association_' . sha1($server_url) . '_' . sha1($handle);
    }
    
    /**
     * Memcache key is prefixed with 'openid_association_' string. 
     */
    function associationServerKey($server_url) 
    {
        return 'openid_association_server_' . sha1($server_url);
    }
    
    /**
     * Report that this storage doesn't support cleanup
     */
    function supportsCleanup()
    {
        return false;
    }
}

