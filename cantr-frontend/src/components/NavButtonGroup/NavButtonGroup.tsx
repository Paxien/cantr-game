import React, {FC} from "react";
import Button from "react-bootstrap/Button";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import {useTranslation} from "react-i18next";
import {
  faAlignJustify,
  faBook,
  faCog,
  faComments,
  faSignOutAlt,
  IconDefinition
} from "@fortawesome/free-solid-svg-icons";
import {faDiscord} from "@fortawesome/free-brands-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import styles from "./NavButtonGroup.module.scss";

type Props = {
  children: string,
  icon: IconDefinition,
  url: string,
  openInNewTab?: boolean,
};

const NavButton: FC<Props> = ({children, icon, url, openInNewTab = false}) =>
  <Button as="a"
          className={"bg-transparent " + styles.noBorder}
          href={url}
          target={openInNewTab ? "_blank" : "_self"}>
    <FontAwesomeIcon className={styles.icon} size="2x" icon={icon}/>
    <p className={styles.caption}>{children}</p>
  </Button>;

const NavButtonGroup = () => {
  const {t} = useTranslation();
  return (
    <Container fluid>
      <Row noGutters>
        <Col className="btn-group" xs={12} sm={6}>
          <NavButton key="discord" icon={faDiscord} url="https://discordapp.com/invite/rpquAWT" openInNewTab>
            {t("player_nav_chat")}
          </NavButton>
          <NavButton key="forum" icon={faAlignJustify} url="https://forum.cantr.org/" openInNewTab>
            {t("player_nav_forum")}
          </NavButton>
          <NavButton key="wiki" icon={faBook} url={t("intro_wiki_language_link")} openInNewTab>
            {t("player_nav_wiki")}
          </NavButton>
        </Col>
        <Col className="btn-group" xs={12} sm={6}>
          <NavButton key="contact" icon={faComments} url="index.php?page=contact">
            {t("player_nav_contact")}
          </NavButton>
          <NavButton key="settings" icon={faCog} url="index.php?page=settings">
            {t("player_nav_settings")}
          </NavButton>
          <NavButton key="logout" icon={faSignOutAlt} url="index.php?page=logout">
            {t("player_nav_logout")}
          </NavButton>
        </Col>
      </Row>
    </Container>
  );
}

export default NavButtonGroup;
