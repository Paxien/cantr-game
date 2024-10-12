import React from 'react';
import Button from "react-bootstrap/Button";
import Navbar from "react-bootstrap/Navbar";
import Nav from "react-bootstrap/Nav";
import Form from "react-bootstrap/Form";
import FormControl from "react-bootstrap/FormControl";
import {useTranslation} from 'react-i18next';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faEdit} from '@fortawesome/free-solid-svg-icons'

const PlayerNavBar = () => {
  const {t} = useTranslation();
  return (
    <Navbar fixed="top" bg="dark" variant="dark">
      <Navbar.Brand href="#home">{t("you_have", {"OBJECT": "hammer"})}</Navbar.Brand>
      <Nav className="mr-auto">
        <Nav.Link href="#home">gaweq</Nav.Link>
        <Nav.Link href="#pricing">
          <FontAwesomeIcon icon={faEdit}/>
          {t("player_nav_forum")}</Nav.Link>
      </Nav>
      <Form inline>
        <FormControl type="text" placeholder="Search" className="mr-sm-2"/>
        <Button variant="outline-info">Search</Button>
      </Form>
    </Navbar>
  );
};

export default PlayerNavBar;