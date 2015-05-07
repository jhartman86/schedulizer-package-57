<?php namespace Concrete\Package\Schedulizer\Src\Persistable\Mixins {

    use Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspector;
    use Concrete\Package\Schedulizer\Src\Persistable\Handler;

    /**
     * Methods are declared final as they shouldn't be overridden; instead you
     * should use the available hooks.
     * Class Crud
     * @package Concrete\Package\Schedulizer\Src\Persistable\Mixins
     */
    trait Crud {

        use Fetchers, Hooks, SettersSerializers;

        /**
         * @var $id int
         * @definition({"cast":"int", "declarable":false})
         */
        protected $id;

        /** @return int|null */
        public function getID(){ return $this->id; }

        /**
         * Get an instance by ID
         * @param $id
         * @return $this|void
         */
        public static function getByID( $id ){
            return static::fetchOneBy(function(\PDO $connection, $tableName) use ($id){
                $statement = $connection->prepare("SELECT * FROM {$tableName} WHERE id=:id");
                $statement->bindValue(':id', $id);
                return $statement;
            });
        }

        /**
         * Is the entity persisted?
         * @return bool
         */
        public function isPersisted(){
            return (bool)((int)$this->id >= 1);
        }

        /**
         * Create a new instance
         * @param $data
         * @return $this
         */
        public static function create( $data ){
            $instance = new static();
            $instance->mergePropertiesFrom($data);
            return $instance->save();
        }

        /**
         * Update an instance
         * @param $data
         * @return $this
         */
        public function update( $data ){
            $this->mergePropertiesFrom($data);
            return $this->save();
        }

        /**
         * Both create and update proxy to this method, or it can be called directly
         * after doing some work on an entity. Lots of flexibility.
         * @return $this
         */
        public function save(){
            $this->onBeforePersist();
            $handler = new Handler(DefinitionInspector::parse($this), $this);
            $handler->commit();
            $this->onAfterPersist();
            return $this;
        }

        /**
         * Delete the entity record.
         */
        public function delete(){
            $this->onBeforeDelete();
            $id = $this->id;
            static::adhocQuery(function(\PDO $connection, $tableName) use ($id){
                $statement = $connection->prepare("DELETE FROM {$tableName} WHERE id=:id");
                $statement->bindValue(':id', $id);
                return $statement;
            });
            $this->onAfterDelete();
        }

    }

}