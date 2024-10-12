import React from "react";
import {faUserPlus} from "@fortawesome/free-solid-svg-icons";
import styles from "./NewCharacterButton.module.scss";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import Button from "react-bootstrap/Button";
import {useTranslation} from "react-i18next";
import classNames from "classnames";

const NewCharacterButton = () => {
  const {t} = useTranslation();
  return (
    <div className="react-component">
      <Button as="a" href="index.php?page=addchar" className={classNames("bg-transparent", styles.button)}>
        <FontAwesomeIcon className={styles.icon} icon={faUserPlus}/>
        <span>{t("create_new_character")}</span>
      </Button>
    </div>
  );
};

export default NewCharacterButton;
