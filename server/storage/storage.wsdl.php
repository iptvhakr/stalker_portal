<?
/**
 * @deprecated since version 4.7.3
 */
include "../common.php";

echo "<?xml version='1.0' encoding='UTF-8'?>";

$storage_ip = Mysql::getInstance()->from('storages')->where(array('id' => intval(@$_GET['id'])))->get()->first('storage_ip');

?>
<definitions name="master" targetNamespace="urn:master" xmlns:typens="urn:master" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns="http://schemas.xmlsoap.org/wsdl/">
	<message name="__construct"/>
	<message name="__constructResponse"/>
	<message name="checkDir">
		<part name="name" type="xsd:string"/>
		<part name="media_type" type="xsd:string"/>
	</message>
	<message name="checkDirResponse">
		<part name="checkDirReturn" type="xsd:anyType"/>
	</message>
	<message name="checkHomeDir">
		<part name="mac" type="xsd:string"/>
	</message>
	<message name="checkHomeDirResponse">
		<part name="checkHomeDirReturn" type="xsd:boolean"/>
	</message>
	<message name="createDir">
		<part name="name1" type="xsd:string"/>
	</message>
	<message name="createDirResponse">
		<part name="createDirReturn" type="xsd:boolean"/>
	</message>
	<message name="createLink">
		<part name="mac1" type="xsd:string"/>
		<part name="dir" type="xsd:string"/>
		<part name="file" type="xsd:string"/>
		<part name="media_id" type="xsd:integer"/>
		<part name="media_type1" type="xsd:string"/>
	</message>
	<message name="createLinkResponse">
		<part name="createLinkReturn" type="xsd:boolean"/>
	</message>
	<message name="startMD5Sum">
		<part name="media_name" type="xsd:string"/>
	</message>
	<message name="startMD5SumResponse"/>
	<message name="stopMD5Sum">
		<part name="media" type="xsd:string"/>
	</message>
	<message name="stopMD5SumResponse"/>
    <message name="startRecording">
		<part name="url" type="xsd:string"/>
		<part name="ch_id" type="xsd:integer"/>
	</message>
    <message name="startRecordingResponse">
		<part name="startRecordingReturn" type="xsd:string"/>
	</message>
    <message name="stopRecording">
		<part name="ch_id1" type="xsd:integer"/>
	</message>
    <message name="stopRecordingResponse">
		<part name="stopRecordingReturn" type="xsd:boolean"/>
	</message>
	<portType name="StoragePortType">
		<operation name="__construct">
			<input message="typens:__construct"/>
			<output message="typens:__constructResponse"/>
		</operation>
		<operation name="checkDir">
			<documentation>
				Check directory and return list of media files
			</documentation>
			<input message="typens:checkDir"/>
			<output message="typens:checkDirResponse"/>
		</operation>
		<operation name="checkHomeDir">
			<documentation>
				Create stb home directory by MAC or clean it
			</documentation>
			<input message="typens:checkHomeDir"/>
			<output message="typens:checkHomeDirResponse"/>
		</operation>
		<operation name="createDir">
			<documentation>
				Create directory for video
			</documentation>
			<input message="typens:createDir"/>
			<output message="typens:createDirResponse"/>
		</operation>
		<operation name="createLink">
			<documentation>
				Create hard link $file in stb home directory
			</documentation>
			<input message="typens:createLink"/>
			<output message="typens:createLinkResponse"/>
		</operation>
		<operation name="startMD5Sum">
			<documentation>
				Start counting MD5 SUM for media
			</documentation>
			<input message="typens:startMD5Sum"/>
			<output message="typens:startMD5SumResponse"/>
		</operation>
		<operation name="stopMD5Sum">
			<documentation>
				Stops process, which counting MD5 SUM for media
			</documentation>
			<input message="typens:stopMD5Sum"/>
			<output message="typens:stopMD5SumResponse"/>
		</operation>
        <operation name="startRecording">
			<documentation>
				Start stream recording
			</documentation>
			<input message="typens:startRecording"/>
			<output message="typens:startRecordingResponse"/>
		</operation>
        <operation name="stopRecording">
			<documentation>
				Stop stream recording
			</documentation>
			<input message="typens:stopRecording"/>
			<output message="typens:stopRecordingResponse"/>
		</operation>
	</portType>
	<binding name="StorageBinding" type="typens:StoragePortType">
		<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
		<operation name="__construct">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
		<operation name="checkDir">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
		<operation name="checkHomeDir">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
		<operation name="createDir">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
		<operation name="createLink">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
		<operation name="startMD5Sum">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
		<operation name="stopMD5Sum">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
        <operation name="startRecording">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
        <operation name="stopRecording">
			<soap:operation soapAction="urn:StorageAction"/>
			<input>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</input>
			<output>
				<soap:body namespace="urn:master" use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</output>
		</operation>
	</binding>
	<service name="masterService">
		<port name="StoragePort" binding="typens:StorageBinding">
			<soap:address location="http://<? echo $storage_ip ?>/stalker_portal/storage/storage.php"/>
		</port>
	</service>
</definitions>
